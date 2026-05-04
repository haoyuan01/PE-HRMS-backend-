<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeBranchFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('uuid') && !empty($filters->uuid))
        {
            $data->where('uuid', $filters->uuid);
        }
        
        if ($filters->has('name') && !empty($filters->name))
        {
            $data->where('name', 'like', "%$filters->name%");
        }

        if ($filters->has('phone') && !empty($filters->phone))
        {
            $data->where(DB::raw("CONCAT_WS(phone_code,'',phone_number)"), 'like', "%$filters->phone%");
        }

        if ($filters->has('phone_code') && !empty($filters->phone_code))
        {
            $data->where('phone_code', $filters->phone_code);
        }

        if ($filters->has('fax') && !empty($filters->fax))
        {
            $data->where(DB::raw("CONCAT_WS(fax_code,'',fax_number)"), 'like', "%$filters->fax%");
        }

        if ($filters->has('fax_code') && !empty($filters->fax_code))
        {
            $data->where('fax_code', $filters->fax_code);
        }

        if ($filters->has('email') && !empty($filters->email))
        {
            $data->where('email', 'like', "%$filters->email%");
        }
        
        if ($filters->has('address') && !empty($filters->address))
        {
            $data->where(DB::raw("CONCAT_WS(address_1,'',address_2,'',address_3)"), 'like', "%$filters->address%");
        }

        if ($filters->has('city') && !empty($filters->city))
        {
            $data->where('city', 'like', "%$filters->city%");
        }

        if ($filters->has('state') && !empty($filters->state))
        {
            $data->where('state', 'like', "%$filters->state%");
        }

        if ($filters->has('postcode') && !empty($filters->postcode))
        {
            $data->where('postcode', 'like', "%$filters->postcode%");
        }

        if ($filters->has('country') && !empty($filters->country))
        {
            $data->where('country', 'like', "%$filters->country%");
        }

        if ($filters->has('is_active') && $filters->is_active >= 0)
        {
            $data->where('is_active', "$filters->is_active");
        }

        if ($filters->has('search_words') && !empty($filters->search_words))
        {
            $data->where(function($query) use ($filters) {
                foreach ($filters->search_words as $word) {
                    $query->where('name', 'like', "%$word%")
                        ->orWhere('uuid', $word);
                        //   ->orWhere(DB::raw("CONCAT_WS(address_1,'',address_2,'',address_3)"), 'like', "%$word%")
                        //   ->orWhere(DB::raw("CONCAT_WS(phone_code,'',phone_number)"), 'like', "%$word%")
                        //   ->orWhere(DB::raw("CONCAT_WS(fax_code,'',fax_number)"), 'like', "%$word%")
                        //   ->orWhere('email', 'like', "%$word%")
                        //   ->orWhere('city', 'like', "%$word%")
                        //   ->orWhere('state', 'like', "%$word%")
                        //   ->orWhere('postcode', 'like', "%$word%")
                        //   ->orWhere('country', 'like', "%$word%")
                        //   ->orWhere('uuid', $word);
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}