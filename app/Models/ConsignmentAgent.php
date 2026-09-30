<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ConsignmentAgent extends Model implements AuditableContract, HasMedia
{
    use Auditable, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'address',
        'id_type',
        'id_number'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('id_photo')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('portrait')
            ->singleFile()
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 600, 600)
            ->nonQueued()
            ->performOnCollections(['id_photo', 'portrait']);
    }
}
