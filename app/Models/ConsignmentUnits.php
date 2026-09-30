<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ConsignmentUnits extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'unit_id',
        'storage_id',
        'consignment_id',
        'price_original',
        'profit_original',
        'commission',
        'status',
    ];

    public function unit()
    {
        return $this->belongsTo(ItemUnits::class, 'unit_id');
    }

    public function storage()
    {
        return $this->belongsTo(Storage::class);
    }

    public function consignment()
    {
        return $this->belongsTo(Consignment::class);
    }
}
