<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Filters\ClaimHeaderFilter;
use App\Http\Requests\ClaimHeaderApproveRequest;
use App\Http\Requests\ClaimHeaderIndexRequest;
use App\Http\Requests\ClaimHeaderPaidRequest;
use App\Http\Requests\ClaimHeaderRejectRequest;
use App\Http\Requests\ClaimHeaderShowRequest;
use App\Http\Requests\ClaimHeaderStoreRequest;
use App\Http\Requests\ClaimHeaderUpdateRequest;
use App\Http\Requests\ClaimHeaderUpdateStatusRequest;
use App\Http\Resources\ClaimHeaderResource;
use App\Models\ClaimHeader;
use App\Models\ClaimItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClaimHeaderController extends Controller
{
    public function __construct(private ClaimHeaderFilter $claim_header_filter)
    {
    }

    public function index(ClaimHeaderIndexRequest $request)
    {
        $claim_header = ClaimHeader::with([
            'claimItems',
            'user',
            'approver',
            'payer',
        ])->active();

        $claim_header = $this->claim_header_filter->apply($request, $request->size, $claim_header);

        return self::responsePaginated(ClaimHeaderResource::collection($claim_header), $claim_header);
    }

    public function store(ClaimHeaderStoreRequest $request)
    {
        $approver = $request->approver_uuid ? User::findByUuid($request->approver_uuid)->id : null;

        DB::beginTransaction();

        try {
            $total_amount = collect($request->items)->sum('amount');

            $claim_header = ClaimHeader::create([
                'uuid' => self::uuid(),
                'user_id' => self::auth()->id,
                'approver_id' => $approver,
                'approved_at' => null,
                'payer_id' => null,
                'paid_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
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

            $claim_header->load(['claimItems', 'user', 'approver', 'payer']);

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

        DB::beginTransaction();

        try {
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

            $total_amount = $claim_header->claimItems()->sum('amount');

            $claim_header->update([
                'uuid' => $claim_header->uuid,
                'user_id' => $claim_header->user_id,
                'approver_id' => $request->approver_uuid ? User::findByUuid($request->approver_uuid)->id : $claim_header->approver_id,
                'name' => $request->name,
                'remark' => $request->remark,
                'total_amount' => $total_amount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $claim_header->load(['claimItems', 'user', 'approver', 'payer']);

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

    public function approve(ClaimHeaderApproveRequest $request, string $uuid)
    {
        $claim_header = ClaimHeader::findByUuid($uuid);

        $claim_header->update([
            'approver_id' => self::auth()->id,
            'approved_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $claim_header->load([
            'claimItems',
            'user',
            'approver',
            'payer',
        ]);

        return self::response(new ClaimHeaderResource($claim_header));
    }

    public function paid(ClaimHeaderPaidRequest $request, string $uuid)
    {
        $claim_header = ClaimHeader::findByUuid($uuid);

        $claim_header->update([
            'payer_id' => self::auth()->id,
            'paid_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $claim_header->load([
            'claimItems',
            'user',
            'approver',
            'payer',
        ]);

        return self::response(new ClaimHeaderResource($claim_header));
    }

    public function reject(ClaimHeaderRejectRequest $request, string $uuid)
    {
        $claim_header = ClaimHeader::findByUuid($uuid);

        $claim_header->update([
            'rejected_by' => self::auth()->id,
            'rejected_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $claim_header->load([
            'claimItems',
            'user',
            'approver',
            'payer',
            'rejectedBy',
        ]);

        return self::response(new ClaimHeaderResource($claim_header));
    }
}
