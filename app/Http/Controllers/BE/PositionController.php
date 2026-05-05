<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Requests\PositionIndexRequest;
use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Filters\PositionFilter;
use App\Http\Requests\PositionShowRequest;
use App\Http\Requests\PositionStoreRequest;
use App\Http\Requests\PositionUpdateRequest;
use App\Http\Requests\PositionUpdateStatusRequest;
use App\Http\Resources\PositionResource;

class PositionController extends Controller
{
    public function __construct(private PositionFilter $position_filter)
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
        $position = Position::create([
            'uuid' => self::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => StatusCodeConstants::ACTIVE,
            'created_by' => self::auth()->uuid,
            'created_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new PositionResource($position));
    }

    public function update(PositionUpdateRequest $request, string $uuid)
    {
        $position = Position::findByUuid($uuid);

        $position->update([
            'name' => $request->name,
            'description' => $request->description,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new PositionResource($position));
    }

    public function updateStatus(PositionUpdateStatusRequest $request, string $uuid)
    {
        $position = Position::findByUuid($uuid);

        $position->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new PositionResource($position));
    }

    public function show(PositionShowRequest $request, string $uuid)
    {
        $position = Position::findByUuid($uuid);
        
        return self::response(new PositionResource($position));
    }
}
