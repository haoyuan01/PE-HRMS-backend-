<?php

namespace App\Http\Controllers\BE;

use App\Http\Controllers\Controller;
use App\Constants\StatusCodeConstants;
use App\Models\ClaimItem;
use App\Models\User;
use App\Exceptions\AppException;
use App\Http\Requests\ClaimItemDirectorApproveRequest;
use App\Http\Requests\ClaimItemManagerApproveRequest;
use App\Http\Resources\ClaimItemResource;
use Illuminate\Http\Request;

class ClaimItemController extends Controller
{
    public function __construct()
    {
    }

    public function managerApprove(ClaimItemManagerApproveRequest $request, string $uuid)
    {
        $claim_item = ClaimItem::findByUuid($uuid);

        $manager = User::findByUuid(self::auth()->uuid);

        throw_if($manager->employment?->is_manager != StatusCodeConstants::ACTIVE, AppException::class, 'Manager access only');

        $claim_item->update([
            'manager_action_by' => $manager->id,
            'manager_action_at' => self::currentDateTime(),
            'manager_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
        ]);

        return self::response(new ClaimItemResource($claim_item));
    }

    public function directorApprove(ClaimItemDirectorApproveRequest $request, string $uuid)
    {
        $claim_item = ClaimItem::findByUuid($uuid);

        $director = User::findByUuid(self::auth()->uuid);

        throw_if($director->employment?->is_director != StatusCodeConstants::ACTIVE, AppException::class, 'Director access only');
        throw_if($claim_item->manager_action_at == null, AppException::class, 'Manager not yet approved or rejected');

        $claim_item->update([
            'director_action_by' => $director->id,
            'director_action_at' => self::currentDateTime(),
            'director_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
        ]);

        return self::response(new ClaimItemResource($claim_item));
    }
}
