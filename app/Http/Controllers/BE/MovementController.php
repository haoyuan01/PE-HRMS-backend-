<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\MovementFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\MovementCalendarSummaryRequest;
use App\Http\Requests\MovementIndexRequest;
use App\Http\Requests\MovementShowRequest;
use App\Http\Requests\MovementStoreRequest;
use App\Http\Requests\MovementUpdateRequest;
use App\Http\Requests\MovementUpdateStatusRequest;
use App\Http\Resources\MovementResource;
use App\Models\Movement;
use App\Models\MovementType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MovementController extends Controller
{
    public function __construct(private MovementFilter $movement_filter)
    {
    }

    public function index(MovementIndexRequest $request)
    {
        $movement = Movement::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',
            'movement_type',
        ])->active();

        $movement = $this->movement_filter->apply($request, $request->size, $movement);

        return self::responsePaginated(MovementResource::collection($movement), $movement);
    }

    public function store(MovementStoreRequest $request)
    {
        $user = User::findByUuid($request->user_uuid);
        $movement_type = MovementType::findByUuid($request->movement_type_uuid);

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('movements', $filename, 'public');
            }

            $movement = Movement::create([
                'uuid' => self::uuid(),
                'user_id' => $user->id,
                'movement_type_id' => $movement_type->id,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'attachment_path' => $attachment_path,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $movement->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
                'user.certificates',
                'movement_type',
            ]);

            DB::commit();

            return self::response(new MovementResource($movement));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(MovementUpdateRequest $request, string $uuid)
    {
        $movement = Movement::findByUuid($uuid);
        $movement_type = MovementType::findByUuid($request->movement_type_uuid);

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('movements', $filename, 'public');
            }

            $movement->update([
                'user_id' => $movement->user_id,
                'movement_type_id' => $movement_type->id,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'attachment_path' => $attachment_path ? $attachment_path : $movement->attachment_path,
                'is_active' => StatusCodeConstants::ACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $movement->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
                'user.certificates',
                'movement_type',
            ]);

            DB::commit();

            return self::response(new MovementResource($movement));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(MovementUpdateStatusRequest $request, string $uuid)
    {
        $movement = Movement::findByUuid($uuid);

        $movement->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $movement->load([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',
            'movement_type',
        ]);

        return self::response(new MovementResource($movement));
    }

    public function show(MovementShowRequest $request, string $uuid)
    {
        $movement = Movement::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',
            'movement_type',
        ])->where('uuid', $uuid)->active()->firstOrFail();

        return self::response(new MovementResource($movement));
    }

    public function calendarSummaries(MovementCalendarSummaryRequest $request)
    {
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();

        $movements = Movement::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',
            'movement_type',
        ])
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date->format('Y-m-d'), $end_date->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$start_date->format('Y-m-d'), $end_date->format('Y-m-d')])
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->whereNull('end_date')
                            ->whereBetween('start_date', [$start_date->format('Y-m-d'), $end_date->format('Y-m-d')]);
                    })
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->where('start_date', '<=', $start_date->format('Y-m-d'))
                            ->where('end_date', '>=', $end_date->format('Y-m-d'));
                    });
            })
            ->active()
            ->get();

        $data = [];
        $date = $start_date->copy();

        while($date->lte($end_date))
        {
            $date_key = $date->format('Y-m-d');

            $daily_movements = $movements->filter(function ($movement) use ($date_key) {
                $movement_start_date = Carbon::parse($movement->start_date)->format('Y-m-d');
                $movement_end_date = $movement->end_date ? Carbon::parse($movement->end_date)->format('Y-m-d') : $movement_start_date;

                return $movement_start_date <= $date_key &&
                    $movement_end_date >= $date_key;
            });

            if ($daily_movements->isNotEmpty())
            {
                $data[$date_key] = MovementResource::collection($daily_movements);
            }

            $date->addDay();
        }

        return self::response($data);
    }
}
