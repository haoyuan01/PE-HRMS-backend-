<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\UpcomingEventFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpcomingEventIndexRequest;
use App\Http\Requests\UpcomingEventShowRequest;
use App\Http\Requests\UpcomingEventStoreRequest;
use App\Http\Requests\UpcomingEventUpdateRequest;
use App\Http\Requests\UpcomingEventUpdateStatusRequest;
use App\Http\Resources\UpcomingEventResource;
use App\Models\Department;
use App\Models\Office;
use App\Models\UpcomingEvent;
use App\Models\UpcomingEventDepartment;
use App\Models\UpcomingEventImage;
use App\Models\UpcomingEventOffice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpcomingEventController extends Controller
{
    public function __construct(private UpcomingEventFilter $upcoming_event_filter)
    {
    }

    public function index(UpcomingEventIndexRequest $request)
    {
        $upcoming_event = UpcomingEvent::with([
            'upcomingEventImages',
            'upcomingEventDepartments.department',
            'upcomingEventOffices.office',
        ])->active();

        $upcoming_event = $this->upcoming_event_filter->apply($request, $request->size, $upcoming_event);

        return self::responsePaginated(UpcomingEventResource::collection($upcoming_event), $upcoming_event);
    }

    public function store(UpcomingEventStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $upcoming_event = UpcomingEvent::create([
                'uuid' => self::uuid(),
                'name' => $request->name,
                'description' => $request->description,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_published' => $request->is_published,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->hasFile('images') && is_array($request->images))
            {
                foreach($request->images as $image)
                {
                    $filename = time() . '_' . self::uuid() . '.' . $image->getClientOriginalExtension();

                    $image_path = $image->storeAs('upcoming_events', $filename, 'public');

                    UpcomingEventImage::create([
                        'uuid' => self::uuid(),
                        'upcoming_event_id' => $upcoming_event->id,
                        'image_path' => $image_path,
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'updated_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            if ($request->has('department_uuid') && $request->department_uuid)
            {
                foreach($request->department_uuid as $department_uuid)
                {
                    $department = Department::findByUuid($department_uuid);

                    UpcomingEventDepartment::create([
                        'uuid' => self::uuid(),
                        'upcoming_event_id' => $upcoming_event->id,
                        'department_id' => $department->id,
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'updated_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            if ($request->has('office_uuid') && $request->office_uuid)
            {
                foreach($request->office_uuid as $office_uuid)
                {
                    $office = Office::findByUuid($office_uuid);

                    UpcomingEventOffice::create([
                        'uuid' => self::uuid(),
                        'upcoming_event_id' => $upcoming_event->id,
                        'office_id' => $office->id,
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'updated_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            $upcoming_event->load(['upcomingEventImages', 'upcomingEventDepartments.department', 'upcomingEventOffices.office']);

            DB::commit();

            return self::response(new UpcomingEventResource($upcoming_event));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(UpcomingEventUpdateRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $upcoming_event = UpcomingEvent::findByUuid($uuid);

            $upcoming_event->update([
                'name' => $request->name,
                'description' => $request->description,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_published' => $request->is_published,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $image_uuids = [];

            if ($request->has('images') && $request->images)
            {
                foreach($request->images as $index => $image)
                {
                    $image_uuid = $image['uuid'] ?? null;
                    $upcoming_event_image = null;

                    if ($image_uuid)
                    {
                        $image_uuids[] = $image_uuid;

                        $upcoming_event_image = UpcomingEventImage::where('upcoming_event_id', $upcoming_event->id)
                            ->where('uuid', $image_uuid)
                            ->first();
                    }

                    if (!$upcoming_event_image)
                    {
                        $image_path = null;

                        if ($request->hasFile("images.$index.image"))
                        {
                            $file = $request->file("images.$index.image");

                            $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                            $image_path = $file->storeAs('upcoming_events', $filename, 'public');
                        }

                        $upcoming_event_image = UpcomingEventImage::create([
                            'uuid' => self::uuid(),
                            'upcoming_event_id' => $upcoming_event->id,
                            'image_path' => $image_path,
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'created_by' => self::auth()->uuid,
                            'updated_by' => self::auth()->uuid,
                            'created_at' => self::currentDateTime(),
                            'updated_at' => self::currentDateTime(),
                        ]);

                        $image_uuids[] = $upcoming_event_image->uuid;
                    }
                    else
                    {
                        $image_path = $upcoming_event_image->image_path;

                        if ($request->hasFile("images.$index.image"))
                        {
                            if ($upcoming_event_image->image_path && Storage::disk('public')->exists($upcoming_event_image->image_path))
                            {
                                Storage::disk('public')->delete($upcoming_event_image->image_path);
                            }

                            $file = $request->file("images.$index.image");

                            $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                            $image_path = $file->storeAs('upcoming_events', $filename, 'public');
                        }

                        $upcoming_event_image->update([
                            'image_path' => $image_path,
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                }
            }

            UpcomingEventImage::where('upcoming_event_id', $upcoming_event->id)
                ->whereNotIn('uuid', $image_uuids)
                ->update([
                    'is_active' => StatusCodeConstants::INACTIVE,
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

            $department_ids = [];

            if ($request->has('department_uuid') && $request->department_uuid)
            {
                foreach($request->department_uuid as $department_uuid)
                {
                    $department = Department::findByUuid($department_uuid);
                    $department_ids[] = $department->id;

                    $upcoming_event_department = UpcomingEventDepartment::where('upcoming_event_id', $upcoming_event->id)
                        ->where('department_id', $department->id)
                        ->first();

                    if (!$upcoming_event_department)
                    {
                        UpcomingEventDepartment::create([
                            'uuid' => self::uuid(),
                            'upcoming_event_id' => $upcoming_event->id,
                            'department_id' => $department->id,
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'created_by' => self::auth()->uuid,
                            'updated_by' => self::auth()->uuid,
                            'created_at' => self::currentDateTime(),
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                    else
                    {
                        $upcoming_event_department->update([
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                }
            }

            UpcomingEventDepartment::where('upcoming_event_id', $upcoming_event->id)
                ->whereNotIn('department_id', $department_ids)
                ->update([
                    'is_active' => StatusCodeConstants::INACTIVE,
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

            $office_ids = [];

            if ($request->has('office_uuid') && $request->office_uuid)
            {
                foreach($request->office_uuid as $office_uuid)
                {
                    $office = Office::findByUuid($office_uuid);
                    $office_ids[] = $office->id;

                    $upcoming_event_office = UpcomingEventOffice::where('upcoming_event_id', $upcoming_event->id)
                        ->where('office_id', $office->id)
                        ->first();

                    if (!$upcoming_event_office)
                    {
                        UpcomingEventOffice::create([
                            'uuid' => self::uuid(),
                            'upcoming_event_id' => $upcoming_event->id,
                            'office_id' => $office->id,
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'created_by' => self::auth()->uuid,
                            'updated_by' => self::auth()->uuid,
                            'created_at' => self::currentDateTime(),
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                    else
                    {
                        $upcoming_event_office->update([
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                }
            }

            UpcomingEventOffice::where('upcoming_event_id', $upcoming_event->id)
                ->whereNotIn('office_id', $office_ids)
                ->update([
                    'is_active' => StatusCodeConstants::INACTIVE,
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

            $upcoming_event->load(['upcomingEventImages', 'upcomingEventDepartments.department', 'upcomingEventOffices.office']);

            DB::commit();

            return self::response(new UpcomingEventResource($upcoming_event));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(UpcomingEventUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $upcoming_event = UpcomingEvent::findByUuid($uuid);

            if ($upcoming_event->upcomingEventImages->isNotEmpty() && $request->is_active == StatusCodeConstants::INACTIVE)
            {
                $upcoming_event->upcomingEventImages->each(function ($image) {
                    
                    if ($image->image_path && Storage::disk('public')->exists($image->image_path))
                    {
                        Storage::disk('public')->delete($image->image_path);
                    }

                    $image->update([
                        'image_path' => null,
                        'is_active' => StatusCodeConstants::INACTIVE,
                        'updated_by' => self::auth()->uuid,
                        'updated_at' => self::currentDateTime(),
                    ]);

                });
            }

            $upcoming_event->update([
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);
            
            DB::commit();

            return self::response(new UpcomingEventResource($upcoming_event));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function show(UpcomingEventShowRequest $request, string $uuid)
    {
        $upcoming_event = UpcomingEvent::findByUuid($uuid);
        
        return self::response(new UpcomingEventResource($upcoming_event));
    }
}
