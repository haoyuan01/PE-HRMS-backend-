<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Filters\ClaimHeaderFilter;
use App\Http\Requests\ClaimHeaderIndexRequest;
use App\Http\Requests\ClaimHeaderShowRequest;
use App\Http\Requests\ClaimHeaderStoreRequest;
use App\Http\Requests\ClaimHeaderUpdateRequest;
use App\Http\Requests\ClaimHeaderUpdateStatusRequest;
use App\Http\Resources\ClaimHeaderResource;
use App\Mail\ClaimApplicationMail;
use App\Models\ClaimHeader;
use App\Models\ClaimItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClaimHeaderController extends Controller
{
    public function __construct(private ClaimHeaderFilter $claim_header_filter)
    {
    }

    public function index(ClaimHeaderIndexRequest $request)
    {
        $claim_header = ClaimHeader::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',

            'managerApprover.personal',
            'managerApprover.contact',
            'managerApprover.employment.office',
            'managerApprover.employment.position',
            'managerApprover.employment.department',
            'managerApprover.emergency',

            'claimItems.approvedBy.personal',
            'claimItems.approvedBy.contact',
            'claimItems.approvedBy.employment.office',
            'claimItems.approvedBy.employment.position',
            'claimItems.approvedBy.employment.department',
            'claimItems.approvedBy.emergency',

            'claimItems.rejectedBy.personal',
            'claimItems.rejectedBy.contact',
            'claimItems.rejectedBy.employment.office',
            'claimItems.rejectedBy.employment.position',
            'claimItems.rejectedBy.employment.department',
            'claimItems.rejectedBy.emergency',

            'claimItems.releasedBy.personal',
            'claimItems.releasedBy.contact',
            'claimItems.releasedBy.employment.office',
            'claimItems.releasedBy.employment.position',
            'claimItems.releasedBy.employment.department',
            'claimItems.releasedBy.emergency',
        ])->active();

        $claim_header = $this->claim_header_filter->apply($request, $request->size, $claim_header);

        return self::responsePaginated(ClaimHeaderResource::collection($claim_header), $claim_header);
    }

    public function store(ClaimHeaderStoreRequest $request)
    {
        $user = User::findByUuid(self::auth()->uuid);

        $manager_approver = null;
        if ($request->manager_approver_uuid)
        {
            $manager_approver = User::findByUuid($request->manager_approver_uuid);
        }

        DB::beginTransaction();

        try {
            $total_amount = collect($request->items)->sum('amount');

            $claim_header = ClaimHeader::create([
                'uuid' => self::uuid(),
                'user_id' => self::auth()->id,
                'manager_approver_id' => $manager_approver ? $manager_approver->id : null,
                'name' => $request->name,
                'remark' => $request->remark,
                'total_amount' => $total_amount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->filled('items') && !empty($request->items))
            {
                foreach($request->items as $item)
                {
                    $attachment_path = null;
                    
                    if (isset($item['attachment']) && $item['attachment'])
                    {
                        $filename = time() . '_' . self::uuid() . '.' . $item['attachment']->getClientOriginalExtension();

                        $attachment_path = $item['attachment']->storeAs('claims', $filename, 'public');
                    }

                    ClaimItem::create([
                        'uuid' => self::uuid(),
                        'claim_header_id' => $claim_header->id,
                        'name' => $item['name'],
                        'amount' => $item['amount'],
                        'date' => $item['date'] ?? null,
                        'attachment_path' => $attachment_path,
                        'remark' => $item['remark'] ?? null,
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_by' => self::auth()->uuid,
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            if ($request->manager_approver_uuid)
            {
                $accountants = User::whereHas('employment', function ($query) {
                    $query->where('is_active', StatusCodeConstants::ACTIVE)
                        ->where('is_accountant', StatusCodeConstants::ACTIVE);
                })
                    ->where('is_active', StatusCodeConstants::ACTIVE)
                    ->pluck('email')
                    ->toArray();

                $data = [
                    'name' => trim(($manager_approver->personal?->first_name ?? '') . ' ' . ($manager_approver->personal?->last_name ?? '')) ?: $manager_approver->email,
                    'applicant_name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
                    'applicant_email' => $user->email,
                    'applicant_phone_number' => $user->contact?->phone_number,
                    'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                    'subject' => 'PE Portal - Claim Pending Approval',
                    'claim_header' => $claim_header,
                    'claim_items' => $claim_header->claimItems()->get(),
                    'total_amount' => $total_amount,
                ];

                Mail::to($manager_approver->email)
                    ->cc($accountants ?? [])
                    ->send(new ClaimApplicationMail($data));
            }

            $claim_header->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',

                'managerApprover.personal',
                'managerApprover.contact',
                'managerApprover.employment.office',
                'managerApprover.employment.position',
                'managerApprover.employment.department',
                'managerApprover.emergency',

                'claimItems.approvedBy.personal',
                'claimItems.approvedBy.contact',
                'claimItems.approvedBy.employment.office',
                'claimItems.approvedBy.employment.position',
                'claimItems.approvedBy.employment.department',
                'claimItems.approvedBy.emergency',

                'claimItems.rejectedBy.personal',
                'claimItems.rejectedBy.contact',
                'claimItems.rejectedBy.employment.office',
                'claimItems.rejectedBy.employment.position',
                'claimItems.rejectedBy.employment.department',
                'claimItems.rejectedBy.emergency',

                'claimItems.releasedBy.personal',
                'claimItems.releasedBy.contact',
                'claimItems.releasedBy.employment.office',
                'claimItems.releasedBy.employment.position',
                'claimItems.releasedBy.employment.department',
                'claimItems.releasedBy.emergency',
            ]);

            DB::commit();

            return self::response(new ClaimHeaderResource($claim_header));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(ClaimHeaderUpdateRequest $request, string $uuid)
    {
        $claim_header = ClaimHeader::findByUuid($uuid);

        $user = User::findByUuid(self::auth()->uuid);

        $old_manager_id = $claim_header->manager_approver_id;
        $new_manager = null;

        if ($request->manager_approver_uuid)
        {
            $new_manager = User::findByUuid($request->manager_approver_uuid);
        }

        DB::beginTransaction();

        try {
            if ($request->filled('items') && !empty($request->items))
            {
                $item_uuids = collect($request->items)
                    ->pluck('uuid')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray(); // collect uuid from request

                $claim_header->claimItems() // deactivate items that are not in request
                    ->whereNotIn('uuid', $item_uuids)
                    ->update([
                        'is_active' => StatusCodeConstants::INACTIVE,
                        'updated_by' => self::auth()->uuid,
                        'updated_at' => self::currentDateTime(),
                    ]);

                foreach($request->items as $item) // insert or update
                {
                    $attachment_path = null;

                    if (isset($item['attachment']) && $item['attachment'])
                    {
                        $filename = time() . '_' . self::uuid() . '.' . $item['attachment']->getClientOriginalExtension();

                        $attachment_path = $item['attachment']->storeAs('claims', $filename, 'public');
                    }

                    if (isset($item['uuid']) && $item['uuid'])
                    {
                        $claim_item = ClaimItem::findByUuid($item['uuid']);

                        $claim_item->update([
                            'name' => $item['name'],
                            'amount' => $item['amount'],
                            'date' => $item['date'] ?? null,
                            'attachment_path' => $attachment_path ? $attachment_path : $claim_item->attachment_path,
                            'remark' => $item['remark'] ?? null,
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                    else
                    {
                        ClaimItem::create([
                            'uuid' => self::uuid(),
                            'claim_header_id' => $claim_header->id,
                            'name' => $item['name'],
                            'amount' => $item['amount'],
                            'date' => $item['date'] ?? null,
                            'attachment_path' => $attachment_path,
                            'remark' => $item['remark'] ?? null,
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'created_by' => self::auth()->uuid,
                            'created_at' => self::currentDateTime(),
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                }
            }

            $total_amount = $claim_header->claimItems()->active()->sum('amount');

            $claim_header->update([
                'manager_approver_id' => $request->manager_approver_uuid ? $new_manager->id : $old_manager_id,
                'name' => $request->name,
                'remark' => $request->remark,
                'total_amount' => $total_amount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->manager_approver_uuid && $new_manager->id != $old_manager_id) // only send email if manager is updated
            {
                $accountants = User::whereHas('employment', function ($query) {
                    $query->where('is_active', StatusCodeConstants::ACTIVE)
                        ->where('is_accountant', StatusCodeConstants::ACTIVE);
                })
                    ->where('is_active', StatusCodeConstants::ACTIVE)
                    ->pluck('email')
                    ->toArray();
                    
                $data = [
                    'name' => trim(($new_manager->personal?->first_name ?? '') . ' ' . ($new_manager->personal?->last_name ?? '')) ?: $new_manager->email,
                    'applicant_name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
                    'applicant_email' => $user->email,
                    'applicant_phone_number' => $user->contact?->phone_number,
                    'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                    'subject' => 'PE Portal - Claim Pending Approval',
                    'claim_header' => $claim_header,
                    'claim_items' => $claim_header->claimItems()->get(),
                    'total_amount' => $total_amount,
                ];

                Mail::to($new_manager->email)
                    ->cc($accountants ?? [])
                    ->send(new ClaimApplicationMail($data));
            }

            $claim_header->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',

                'managerApprover.personal',
                'managerApprover.contact',
                'managerApprover.employment.office',
                'managerApprover.employment.position',
                'managerApprover.employment.department',
                'managerApprover.emergency',

                'claimItems.approvedBy.personal',
                'claimItems.approvedBy.contact',
                'claimItems.approvedBy.employment.office',
                'claimItems.approvedBy.employment.position',
                'claimItems.approvedBy.employment.department',
                'claimItems.approvedBy.emergency',

                'claimItems.rejectedBy.personal',
                'claimItems.rejectedBy.contact',
                'claimItems.rejectedBy.employment.office',
                'claimItems.rejectedBy.employment.position',
                'claimItems.rejectedBy.employment.department',
                'claimItems.rejectedBy.emergency',

                'claimItems.releasedBy.personal',
                'claimItems.releasedBy.contact',
                'claimItems.releasedBy.employment.office',
                'claimItems.releasedBy.employment.position',
                'claimItems.releasedBy.employment.department',
                'claimItems.releasedBy.emergency',
            ]);

            DB::commit();
            
            return self::response(new ClaimHeaderResource($claim_header));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(ClaimHeaderUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $claim_header = ClaimHeader::findByUuid($uuid);

            $claim_header->update([
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            DB::commit();

            return self::response(new ClaimHeaderResource($claim_header));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function show(ClaimHeaderShowRequest $request, string $uuid)
    {
        $claim_header = ClaimHeader::findByUuid($uuid);

        return self::response(new ClaimHeaderResource($claim_header));
    }
}
