<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\OvertimeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeIndexRequest;
use App\Http\Requests\OvertimeShowRequest;
use App\Http\Requests\OvertimeStoreRequest;
use App\Http\Requests\OvertimeUpdateRequest;
use App\Http\Requests\OvertimeUpdateStatusRequest;
use App\Http\Resources\OvertimeResource;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OvertimeController extends Controller
{
    public function __construct(private OvertimeFilter $overtime_filter)
    {
    }

    public function index(OvertimeIndexRequest $request)
    {
        $overtime = Overtime::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
        ])->active();

        $overtime = $this->overtime_filter->apply($request, $request->size, $overtime);

        return self::responsePaginated(OvertimeResource::collection($overtime), $overtime);
    }

    public function store(OvertimeStoreRequest $request)
    {
        $user = User::findByUuid(self::auth()->uuid);

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('overtimes', $filename, 'public');
            }

            $overtime = Overtime::create([
                'uuid' => self::uuid(),
                'user_id' => $user->id,
                'description' => $request->description,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'total_days' => $request->total_days ?? $this->calculateTotalDays($request->start_datetime, $request->end_datetime),
                'attachment_path' => $attachment_path,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $overtime->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
            ]);

            DB::commit();

            return self::response(new OvertimeResource($overtime));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(OvertimeUpdateRequest $request, string $uuid)
    {
        $overtime = Overtime::findByUuid($uuid);
        $user = User::findByUuid(self::auth()->uuid);

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('overtimes', $filename, 'public');
            }

            $overtime->update([
                'user_id' => $user->id,
                'description' => $request->description,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'total_days' => $request->total_days ?? $this->calculateTotalDays($request->start_datetime, $request->end_datetime),
                'attachment_path' => $attachment_path ? $attachment_path : $overtime->attachment_path,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $overtime->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
            ]);

            DB::commit();

            return self::response(new OvertimeResource($overtime));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(OvertimeUpdateStatusRequest $request, string $uuid)
    {
        $overtime = Overtime::findByUuid($uuid);

        $overtime->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        DB::commit();

        return self::response(new OvertimeResource($overtime));
    }

    public function show(OvertimeShowRequest $request, string $uuid)
    {
        $overtime = Overtime::findByUuid($uuid);

        return self::response(new OvertimeResource($overtime));
    }

    private function calculateTotalDays($start_datetime, $end_datetime)
    {
        return round(Carbon::parse($start_datetime)->diffInMinutes(Carbon::parse($end_datetime)) / 1440, 2);
    }
}
