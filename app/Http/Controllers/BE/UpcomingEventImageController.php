<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpcomingEventImageUpdateStatusRequest;
use App\Http\Resources\UpcomingEventImageResource;
use App\Models\UpcomingEventImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UpcomingEventImageController extends Controller
{
    public function __construct()
    {
    }

    public function updateStatus(UpcomingEventImageUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();
        
        try {
            $upcoming_event_image = UpcomingEventImage::findByUuid($uuid);
            
            if ($upcoming_event_image->image_path && Storage::disk('public')->exists($upcoming_event_image->image_path) && $request->is_active == StatusCodeConstants::INACTIVE)
            {
                Storage::disk('public')->delete($upcoming_event_image->image_path);
            }

            $upcoming_event_image->update([
                'image_path' => null,
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);
            
            DB::commit();

            return self::response(new UpcomingEventImageResource($upcoming_event_image));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }
}
