<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Storage extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'is_active',
    ];

    public function units()
    {
        return $this->hasMany(ItemUnits::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_units')
            ->withPivot('number', 'status')
            ->withTimestamps();
    }
}