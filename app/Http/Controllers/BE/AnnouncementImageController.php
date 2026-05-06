<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementImageUpdateStatusRequest;
use App\Http\Resources\AnnouncementImageResource;
use App\Models\AnnouncementImage;
use Illuminate\Support\Facades\Storage;

class AnnouncementImageController extends Controller
{
    public function __construct()
    {
    }

    public function updateStatus(AnnouncementImageUpdateStatusRequest $request, string $uuid)
    {
        $announcement_image = AnnouncementImage::findByUuid($uuid);

        if ($announcement_image->image_path && Storage::disk('public')->exists($announcement_image->image_path) && $request->is_active == StatusCodeConstants::INACTIVE)
        {
            Storage::disk('public')->delete($announcement_image->image_path);
        }

        $announcement_image->update([
            'image_path' => null,
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new AnnouncementImageResource($announcement_image));
    }
}
