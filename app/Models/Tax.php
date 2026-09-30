<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Tax extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'tax';

    protected $fillable = [
        'percentage'
    ];

    /**
     * Always fetch (and cache) the single settings row.
     * Creates it with defaults if it doesn't exist yet.
     */
    public static function current(): self
    {
        return Cache::rememberForever('app-tax', function () {
            return static::firstOrCreate(['id' => 1]);
        });
    }

    /**
     * Clear cache whenever settings are saved, so changes
     * take effect immediately app-wide.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('app-tax');
        });
    }
}
