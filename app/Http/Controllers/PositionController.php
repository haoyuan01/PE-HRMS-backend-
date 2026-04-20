<?php

namespace App\Http\Controllers;

use App\Constants\StatusCodeConstants;
use App\Http\Requests\PositionIndexRequest;
use App\Models\Position;
use App\Filters\PositionFilter;
use App\Http\Requests\PositionShowRequest;
use App\Http\Requests\PositionStoreRequest;
use App\Http\Requests\PositionUpdateRequest;
use App\Http\Requests\PositionUpdateStatusRequest;
use App\Http\Resources\PositionResource;
use App\Services\PositionService;

class PositionController extends Controller
{
    public function __construct(private PositionFilter $position_filter, private PositionService $position_service)
    {
    }

    public function index(PositionIndexRequest $request)
    {
        $position = Position::query();

        $position = $this->position_filter->apply($request, $request->size, $position);

        return self::responsePaginated(PositionResource::collection($position), $position);
    }

    public function store(PositionStoreRequest $request)
    {
        $position = new Position();
        $position->uuid = self::uuid();
        $position->name = $request->name;
        $position->description = $request->description;
        $position->is_active = StatusCodeConstants::ACTIVE;
        $position->created_by = $position->updated_by = self::auth()->uuid;
        $position->created_at = $position->updated_at = self::currentDateTime();
        $position->save();
        
        return self::response(new PositionResource($position));
    }

    public function update(PositionUpdateRequest $request, $uuid)
    {
        $position = $this->position_service->findByUUID($uuid);
        $position->name = $request->name;
        $position->description = $request->description;
        $position->updated_by = self::auth()->uuid;
        $position->updated_at = self::currentDateTime();
        $position->save();
        
        return self::response(new PositionResource($position));
    }

    public function updateStatus(PositionUpdateStatusRequest $request, $uuid)
    {
        $position = $this->position_service->findByUUID($uuid);
        $position->is_active = $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE;
        $position->updated_by = self::auth()->uuid;
        $position->updated_at = self::currentDateTime();
        $position->save();
        
        return self::response(new PositionResource($position));
    }

    public function show(PositionShowRequest $request, $uuid)
    {
        $position = $this->position_service->findByUUID($uuid);
        
        return self::response(new PositionResource($position));
    }
}
