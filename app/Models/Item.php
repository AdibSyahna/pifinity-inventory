<?php

namespace App\Models;

use App\Utilities\TaxService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Item extends Model implements AuditableContract, HasMedia
{
    use Auditable, HasTags, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price_item',
        'price_acc',
        'price_ship',
        'price_min_profit',
        'price_max_profit',
        // 'price_tax',
        // 'price_round',
        'weight',
        'is_active',
        'category_id',
        'color_id',
        'size_id',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued()
            ->performOnCollections('default');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function units()
    {
        return $this->hasMany(ItemUnits::class);
    }

    public function storages()
    {
        return $this->belongsToMany(Storage::class, 'item_units')
            ->withPivot('number', 'status')
            ->withTimestamps();
    }

    /**
     * The final (taxed, rounded) selling price, matching the live
     * calculation in ItemResource::updateCalculations().
     */
    public function priceFinal(): float
    {
        return $this->priceBreakdown()['total_price'];
    }

    /**
     * The final total profit, matching the live calculation in
     * ItemResource::updateCalculations().
     */
    public function profitFinal(): float
    {
        return $this->priceBreakdown()['total_profit'];
    }

    /**
     * Shared price breakdown used by the item form and consignments.
     */
    private function priceBreakdown(): array
    {
        $tax = (int) Tax::firstOrFail()->percentage;
        $base = (float) $this->price_item;
        $acc = (float) $this->price_acc;
        $ship = (float) $this->price_ship;
        $minProfit = (float) $this->price_mprofit;
        $gross = $base + $acc + $ship;
        $final = TaxService::calculateTax($gross, $tax, $minProfit);
        return $final;
    }
}
