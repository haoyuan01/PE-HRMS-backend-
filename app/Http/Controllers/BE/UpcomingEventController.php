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
use App\Models\UpcomingEvent;
use App\Models\UpcomingEventImage;
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

            $upcoming_event->load(['upcomingEventImages']);

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

            $upcoming_event->load(['upcomingEventImages']);

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
