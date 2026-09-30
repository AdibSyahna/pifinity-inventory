<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;

class Color extends Model implements AuditableContracts
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'hex_rgb',
        'code'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
