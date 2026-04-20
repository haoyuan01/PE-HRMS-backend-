<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeBranch extends Model
{
    use HasFactory;

    protected $table = 'office_branches';
    public $timestamps = false;
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'address_1',
        'address_2',
        'address_3',
        'city',
        'state',
        'postcode',
        'country',
        'phone_code',
        'phone_number',
        'phone_iso',
        'fax_code',
        'fax_number',
        'fax_iso',
        'email',
        'is_active',
        'created_by',
        'updated_by',
    ];
}
