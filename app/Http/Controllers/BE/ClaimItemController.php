<?php

namespace App\Http\Controllers\BE;

use App\Http\Controllers\Controller;
use App\Constants\StatusCodeConstants;
use App\Http\Requests\ClaimItemApproveRequest;
use App\Models\ClaimItem;
use App\Models\User;
use App\Exceptions\AppException;
use App\Http\Requests\ClaimItemRejectRequest;
use App\Http\Requests\ClaimItemReleaseRequest;
use App\Http\Resources\ClaimItemResource;
use Illuminate\Http\Request;

class ClaimItemController extends Controller
{
    public function __construct()
    {
    }

    public function approve(ClaimItemApproveRequest $request, string $uuid)
    {
        $claim_item = ClaimItem::findByUuid($uuid);

        $approver = User::findByUuid(self::auth()->uuid);

        throw_if($approver->employment?->is_manager != StatusCodeConstants::ACTIVE, AppException::class, 'Manager access only');

        $claim_item->update([
            'approved_by' => $approver->id,
            'approved_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $claim_item->load(['approvedBy', 'releasedBy', 'rejectedBy']);
        
        return self::response(new ClaimItemResource($claim_item));
    }

    public function reject(ClaimItemRejectRequest $request, string $uuid)
    {
        $claim_item = ClaimItem::findByUuid($uuid);

        $rejector = User::findByUuid(self::auth()->uuid);

        throw_if($rejector->employment?->is_manager != StatusCodeConstants::ACTIVE && $rejector->employment?->is_accountant != StatusCodeConstants::ACTIVE, AppException::class, 'Manager or Accountant access only');

        $claim_item->update([
            'rejected_by' => $rejector->id,
            'rejected_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $claim_item->load(['approvedBy', 'releasedBy', 'rejectedBy']);

        return self::response(new ClaimItemResource($claim_item));
    }

    public function release(ClaimItemReleaseRequest $request, string $uuid)
    {
        $claim_item = ClaimItem::findByUuid($uuid);

        $releaser = User::findByUuid(self::auth()->uuid);

        throw_if($releaser->employment?->is_manager != StatusCodeConstants::ACTIVE && $releaser->employment?->is_accountant != StatusCodeConstants::ACTIVE, AppException::class, 'Manager or Accountant access only');

        $claim_item->update([
            'released_by' => $releaser->id,
            'released_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $claim_item->load(['approvedBy', 'releasedBy', 'rejectedBy']);

        return self::response(new ClaimItemResource($claim_item));
    }
}
