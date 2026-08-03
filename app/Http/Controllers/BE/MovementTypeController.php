<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\MovementTypeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\MovementTypeIndexRequest;
use App\Http\Requests\MovementTypeShowRequest;
use App\Http\Requests\MovementTypeStoreRequest;
use App\Http\Requests\MovementTypeUpdateRequest;
use App\Http\Requests\MovementTypeUpdateStatusRequest;
use App\Http\Resources\MovementTypeResource;
use App\Models\MovementType;

class MovementTypeController extends Controller
{
    public function __construct(private MovementTypeFilter $movement_type_filter)
    {
    }

    public function index(MovementTypeIndexRequest $request)
    {
        $movement_type = MovementType::query()->active();

        $movement_type = $this->movement_type_filter->apply($request, $request->size, $movement_type);

        return self::responsePaginated(MovementTypeResource::collection($movement_type), $movement_type);
    }

    public function store(MovementTypeStoreRequest $request)
    {
        $movement_type = MovementType::create([
            'uuid' => self::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => StatusCodeConstants::ACTIVE,
            'created_by' => self::auth()->uuid,
            'created_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        return self::response(new MovementTypeResource($movement_type));
    }

    public function update(MovementTypeUpdateRequest $request, string $uuid)
    {
        $movement_type = MovementType::findByUuid($uuid);

        $movement_type->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => StatusCodeConstants::ACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        return self::response(new MovementTypeResource($movement_type));
    }

    public function updateStatus(MovementTypeUpdateStatusRequest $request, string $uuid)
    {
        $movement_type = MovementType::findByUuid($uuid);

        $movement_type->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        return self::response(new MovementTypeResource($movement_type));
    }

    public function show(MovementTypeShowRequest $request, string $uuid)
    {
        $movement_type = MovementType::findByUuid($uuid);

        return self::response(new MovementTypeResource($movement_type));
    }
}
