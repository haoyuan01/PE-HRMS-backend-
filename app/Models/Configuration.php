<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Configuration extends Model
{
    use HasFactory;

    protected $table = 'configurations';
    public $timestamps = false;
    protected $casts = [
        'is_editable' => 'boolean',
        'is_viewable' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'key',
        'value',
        'value_type',
        'description',
        'is_editable',
        'is_viewable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    /**
     * Data Retrieval Methods
     */

    /**
     * Relationships
     */
}
