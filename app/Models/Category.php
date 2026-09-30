<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Category extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'code'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
