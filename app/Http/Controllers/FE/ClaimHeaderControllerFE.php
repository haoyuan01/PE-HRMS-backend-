<?php

namespace App\Http\Controllers\FE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClaimHeaderApproveItemFE;
use App\Http\Requests\ClaimHeaderReviewActionFE;
use App\Mail\ClaimApplicationMail;
use App\Models\ClaimHeader;
use App\Models\ClaimItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class ClaimHeaderControllerFE extends Controller
{
    public function __construct()
    {
    }

    public function reviewClaim(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email)
        {
            return view('claims.review-invalid');
        }

        $user = User::findByEmail($email, false);

        if (!$user || !Password::tokenExists($user, $token))
        {
            return view('claims.review-invalid');
        }

        $claim_header_uuid = $request->query('claim_header_uuid');
        $type = $request->query('type', 'manager');
        $claim_header = ClaimHeader::findByUuid($claim_header_uuid, false);

        if (!$claim_header)
        {
            return view('claims.review-invalid');
        }

        return view('claims.review', [
            'title' => $type == 'manager' ? 'Manager Claim Review' : 'Director Claim Review',
            'token' => $token,
            'email' => $email,
            'claim_header_uuid' => $claim_header_uuid,
            'type' => $type,
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
            'claim_header' => $claim_header,
            'claim_items' => $claim_header->claimItems()->get(),
            'action_url' => url('/claim-header-review'),
            'approve_item_url' => url('/claim-header-review/item'),
            'pending_item_count' => $this->pendingItemCount($claim_header, $type),
        ]);
    }

    public function approveItem(ClaimHeaderApproveItemFE $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user || !Password::tokenExists($user, $request->token))
        {
            return view('claims.review-invalid');
        }

        $claim_header = ClaimHeader::findByUuid($request->claim_header_uuid, false);
        $claim_item = ClaimItem::findByUuid($request->claim_item_uuid, false);

        if (!$claim_header || !$claim_item || $claim_item->claim_header_id != $claim_header->id)
        {
            return view('claims.review-invalid');
        }

        $type = $request->type ?? 'manager';

        if (
            ($type == 'manager' && $claim_header->manager_reviewed_at) ||
            ($type == 'director' && ($claim_header->manager_reviewed_at == null || $claim_header->director_reviewed_at || $claim_item->manager_action_at == null))
        )
        {
            return view('claims.review-invalid');
        }

        DB::beginTransaction();

        try {
            if ($type == 'manager')
            {
                $claim_item->update([
                    'manager_action_by' => $user->id,
                    'manager_action_at' => self::currentDateTime(),
                    'manager_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);
            }
            else
            {
                $claim_item->update([
                    'director_action_by' => $user->id,
                    'director_action_at' => self::currentDateTime(),
                    'director_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);
            }

            DB::commit();

            return redirect('/claim-header-review?token=' . $request->token . '&email=' . urlencode($request->email) . '&claim_header_uuid=' . $claim_header->uuid . '&type=' . $type);

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function reviewClaimAction(ClaimHeaderReviewActionFE $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user || !Password::tokenExists($user, $request->token))
        {
            return view('claims.review-invalid');
        }

        $claim_header = ClaimHeader::findByUuid($request->claim_header_uuid, false);

        if (!$claim_header)
        {
            return view('claims.review-invalid');
        }

        $type = $request->type ?? 'manager';

        if ( ($type == 'manager' && $claim_header->manager_reviewed_at) || ($type == 'director' && ($claim_header->manager_reviewed_at == null || $claim_header->director_reviewed_at)))
        {
            return view('claims.review-invalid');
        }

        if ($this->pendingItemCount($claim_header, $type) > 0)
        {
            return view('claims.review-invalid');
        }

        DB::beginTransaction();

        try {
            if ($type == 'manager')
            {
                $claim_header->update([
                    'manager_reviewed_by' => $user->id,
                    'manager_reviewed_at' => self::currentDateTime(),
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

                Password::deleteToken($user);

                if ($request->approve)
                {
                    $directors = User::whereHas('employment', function ($query) {
                        $query->where('is_director', '=', StatusCodeConstants::ACTIVE);
                    })
                        ->where('is_active', StatusCodeConstants::ACTIVE)
                        ->get();

                    foreach($directors as $director)
                    {
                        Password::deleteToken($director);

                        $token = Password::createToken($director);

                        $data = [
                            'name' => trim(($director->personal?->first_name ?? '') . ' ' . ($director->personal?->last_name ?? '')) ?: $director->email,
                            'applicant_name' => trim(($claim_header->user->personal?->first_name ?? '') . ' ' . ($claim_header->user->personal?->last_name ?? '')) ?: $claim_header->user->email,
                            'applicant_email' => $claim_header->user->email,
                            'applicant_phone_number' => $claim_header->user->contact?->phone_number,
                            'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                            'subject' => 'PE Portal - Claim Pending Director Approval',
                            'title' => 'Claim Pending Director Approval',
                            'claim_header' => $claim_header,
                            'claim_items' => $claim_header->claimItems()->get(),
                            'total_amount' => $claim_header->total_amount,
                            'action_url' => url('/claim-header-review?token=' . $token . '&email=' . urlencode($director->email) . '&claim_header_uuid=' . $claim_header->uuid . '&type=director'),
                            'action_label' => 'Review Claim',
                        ];

                        Mail::to($director->email)->send(new ClaimApplicationMail($data));
                    }
                }
            }
            else
            {
                $claim_header->update([
                    'director_reviewed_by' => $user->id,
                    'director_reviewed_at' => self::currentDateTime(),
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

                $this->sendAccountantEmail($claim_header);

                Password::deleteToken($user);
            }

            DB::commit();

            return redirect('/claim-header-review-success');

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function reviewClaimSuccess()
    {
        return view('claims.review-success');
    }

    private function pendingItemCount($claim_header, $type)
    {
        if ($type == 'manager')
        {
            return $claim_header->claimItems()
                ->where('manager_action_at', '=', null)
                ->count();
        }

        return $claim_header->claimItems()
            ->where('director_action_at', '=', null)
            ->count();
    }

    private function sendAccountantEmail($claim_header)
    {
        $accountants = User::whereHas('employment', function ($query) {
            $query->where('is_accountant', '=', StatusCodeConstants::ACTIVE);
        })
            ->where('is_active', StatusCodeConstants::ACTIVE)
            ->get();

        foreach($accountants as $accountant)
        {
            $data = [
                'name' => trim(($accountant->personal?->first_name ?? '') . ' ' . ($accountant->personal?->last_name ?? '')) ?: $accountant->email,
                'applicant_name' => trim(($claim_header->user->personal?->first_name ?? '') . ' ' . ($claim_header->user->personal?->last_name ?? '')) ?: $claim_header->user->email,
                'applicant_email' => $claim_header->user->email,
                'applicant_phone_number' => $claim_header->user->contact?->phone_number,
                'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                'subject' => 'PE Portal - Claim Approved',
                'title' => 'Claim Approved',
                'status_text' => 'approved and pending accounting processing',
                'footer_message' => 'Please log in to PE Portal to process the claim application.',
                'claim_header' => $claim_header,
                'claim_items' => $claim_header->claimItems()->get(),
                'total_amount' => $claim_header->total_amount,
            ];

            Mail::to($accountant->email)->send(new ClaimApplicationMail($data));
        }
    }
}
