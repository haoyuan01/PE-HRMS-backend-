<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\AnnouncementFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementIndexRequest;
use App\Http\Requests\AnnouncementShowRequest;
use App\Http\Requests\AnnouncementStoreRequest;
use App\Http\Requests\AnnouncementUpdateRequest;
use App\Http\Requests\AnnouncementUpdateStatusRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\AnnouncementImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementFilter $announcement_filter)
    {
    }

    public function index(AnnouncementIndexRequest $request)
    {
        $announcement = Announcement::with([
            'announcementImages',
        ])->active();

        $announcement = $this->announcement_filter->apply($request, $request->size, $announcement);

        return self::responsePaginated(AnnouncementResource::collection($announcement), $announcement);
    }

    public function store(AnnouncementStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $announcement = Announcement::create([
                'uuid' => self::uuid(),
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_published' => $request->is_published,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->hasFile('images') && !empty($request->images))
            {
                foreach($request->images as $image)
                {
                    $filename = time() . '_' . self::uuid() . '.' . $image->getClientOriginalExtension();

                    $image_path = $image->storeAs('announcements', $filename, 'public');

                    AnnouncementImage::create([
                        'uuid' => self::uuid(),
                        'announcement_id' => $announcement->id,
                        'image_path' => $image_path,
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'updated_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            $announcement->load(['announcementImages']);

            DB::commit();

            return self::response(new AnnouncementResource($announcement));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(AnnouncementUpdateRequest $request, string $uuid)
    {
        $announcement = Announcement::findByUuid($uuid);
        
        DB::beginTransaction();

        try {
            $announcement->update([
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_published' => $request->is_published,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->hasFile('images') && !empty($request->images))
            {
                foreach($request->images as $image)
                {
                    $filename = time() . '_' . self::uuid() . '.' . $image->getClientOriginalExtension();

                    $image_path = $image->storeAs('announcements', $filename, 'public');

                    AnnouncementImage::create([
                        'uuid' => self::uuid(),
                        'announcement_id' => $announcement->id,
                        'image_path' => $image_path,
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'updated_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }
            
            $announcement->load(['announcementImages']);

            DB::commit();
            
            return self::response(new AnnouncementResource($announcement));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(AnnouncementUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $announcement = Announcement::findByUuid($uuid);
            
            if ($announcement->announcementImages->isNotEmpty() && $request->is_active == StatusCodeConstants::INACTIVE)
            {
                $announcement->announcementImages->each(function ($image) {

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

            $announcement->update([
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);
            
            DB::commit();

            return self::response(new AnnouncementResource($announcement));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function show(AnnouncementShowRequest $request, string $uuid)
    {
        $announcement = Announcement::findByUuid($uuid);
        
        return self::response(new AnnouncementResource($announcement));
    }

}
