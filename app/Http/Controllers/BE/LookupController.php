<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\LookupSearchRequest;
use App\Http\Resources\LookupResource;
use App\Models\Department;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LookupController extends Controller
{
    public function __construct()
    {
    }
    
    public function permissions(LookupSearchRequest $request)
    {
        $query = Permission::query()->active();

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
            // ->limit(100)
            ->get();

        return self::response(LookupResource::collection($data));
    }

    public function users(LookupSearchRequest $request)
    {
        $query = User::query()->active();

        if ($request->filled('search_words'))
        {
            $query->where(function ($q) use ($request) {
                foreach ($request->search_words as $word) {
                    $q->orWhere(function ($sub) use ($word) {
                        $sub->whereHas('personal', function($query) use ($word) {
                                $query->where('full_name', 'like', "%$word%")
                                    ->orWhere('first_name', 'like', "%$word%")
                                    ->orWhere('last_name', 'like', "%$word%")
                                    ->orWhere('email', 'like', "%$word%");
                            })
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
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
        ]);
        
        return self::response($data);
    }

    public function claimApprovers(LookupSearchRequest $request)
    {
        $permission = Permission::query()
            ->where('code', 'claim_header_approve')
            ->active()
            ->first();

        $query = User::query()->active();

        if ($permission)
        {
            $query->permission($permission);
        }
        else
        {
            $query->whereNull('id');
        }

        if ($request->filled('search_words'))
        {
            $query->where(function ($q) use ($request) {
                foreach ($request->search_words as $word) {
                    $q->orWhere(function ($sub) use ($word) {
                        $sub->whereHas('personal', function($query) use ($word) {
                                $query->where('full_name', 'like', "%$word%")
                                    ->orWhere('first_name', 'like', "%$word%")
                                    ->orWhere('last_name', 'like', "%$word%")
                                    ->orWhere('email', 'like', "%$word%");
                            })
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
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
        ]);
        
        return self::response($data);
    }

    public function roles(LookupSearchRequest $request)
    {
        $query = Role::query()->active();

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

        $data = $data->map(fn ($role) => [
            'uuid' => $role->uuid,
            'name' => $role->name,
        ]);
        
        return self::response($data);
    }

    public function departments(LookupSearchRequest $request)
    {
        $query = Department::query()->active();

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
        $query = Position::query()->active();

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

    public function offices(LookupSearchRequest $request)
    {
        $query = Office::query()->active();

        if ($request->filled('search_words'))
        {
            $query->where(function ($q) use ($request) {
                foreach ($request->search_words as $word) {
                    $q->orWhere(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%$word%");
                            // ->orWhere(DB::raw("CONCAT_WS(address_1,'',address_2,'',address_3)"), 'like', "%$word%")
                            // ->orWhere('phone_number', 'like', "%$word%")
                            // ->orWhere('fax_number', 'like', "%$word%")
                            // ->orWhere('email', 'like', "%$word%")
                            // ->orWhere('city', 'like', "%$word%")
                            // ->orWhere('state', 'like', "%$word%")
                            // ->orWhere('postcode', 'like', "%$word%")
                            // ->orWhere('country', 'like', "%$word%")
                            // ->orWhere('uuid', $word);
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
