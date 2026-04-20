<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\LookupSearchRequest;
use App\Http\Resources\LookupResource;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function __construct()
    {
    }
    
    public function permissions(LookupSearchRequest $request)
    {
        $query = Permission::query()
            ->where('is_active', StatusCodeConstants::ACTIVE);

        if ($request->filled('search_words'))
        {
            $query->where(function ($q) use ($request) {
                foreach ($request->search_words as $word) {
                    $q->orWhere(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%$word%")
                            ->orWhere('code', 'like', "%$word%")
                            ->orWhere('uuid', $word);
                    });
                }
            });
        }

        $data = $query->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        return self::response(LookupResource::collection($data));
    }

    public function users(LookupSearchRequest $request)
    {
        $query = User::query()
            ->where('is_active', StatusCodeConstants::ACTIVE);

        if ($request->filled('search_words'))
        {
            $query->where(function ($q) use ($request) {
                foreach ($request->search_words as $word) {
                    $q->orWhere(function ($sub) use ($word) {
                        $sub->where('username', 'like', "%$word%")
                            ->orWhere('first_name', 'like', "%$word%")
                            ->orWhere('last_name', 'like', "%$word%")
                            ->orWhere('full_name', 'like', "%$word%")
                            ->orWhere('email', 'like', "%$word%")
                            ->orWhere('uuid', $word);
                    });
                }
            });
        }

        $data = $query->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        $data = $data->map(fn ($user) => [
            'uuid' => $user->uuid,
            'name' => $user->email,
        ]);
        
        return self::response($data);
    }

    public function departments(LookupSearchRequest $request)
    {
        $query = Department::query()
            ->where('is_active', StatusCodeConstants::ACTIVE);

        if ($request->filled('search_words'))
        {
            $query->where(function ($q) use ($request) {
                foreach ($request->search_words as $word) {
                    $q->orWhere(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%$word%")
                            ->orWhere('uuid', $word);
                    });
                }
            });
        }

        $data = $query->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        return self::response(LookupResource::collection($data));
    }

    public function positions(LookupSearchRequest $request)
    {
        $query = Position::query()
            ->where('is_active', StatusCodeConstants::ACTIVE);

        if ($request->filled('search_words'))
        {
            $query->where(function ($q) use ($request) {
                foreach ($request->search_words as $word) {
                    $q->orWhere(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%$word%")
                            ->orWhere('uuid', $word);
                    });
                }
            });
        }

        $data = $query->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        return self::response(LookupResource::collection($data));
    }
}
