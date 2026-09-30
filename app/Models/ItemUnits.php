<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ItemUnits extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'item_id',
        'storage_id',
        'number',
        'code',
        'status'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function storage()
    {
        return $this->belongsTo(Storage::class);
    }
}
