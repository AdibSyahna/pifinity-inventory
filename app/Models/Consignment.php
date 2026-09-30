<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;

class Consignment extends Model implements AuditableContracts
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'agreement_number',
        'status',
        'start_date',
        'duration_days',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date'
    ];

    /**
     * Generate a unique agreement number (CNS-YYYY-XXXX).
     */
    public static function nextAgreementNumber(): string
    {
        $year = date('Y');
        $prefix = 'CNS-' . $year . '-';

        // count this year's agreements
        $last = static::whereYear('created_at', $year)->max('agreement_number');

        $next = is_null($last)
            ? 1
            : ((int) str_replace($prefix, '', (string) $last) + 1);

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the agent that owns the consignment.
     */
    public function agent()
    {
        return $this->belongsTo(ConsignmentAgent::class);
    }

    /**
     * Units assigned to this consignment.
     */
    public function units()
    {
        return $this->hasMany(ConsignmentUnits::class);
    }

    /**
     * The status that should be shown. Falls back to 'overdue' once the
     * end date has been reached or passed (unless it was already finished).
     */
    public function effectiveStatus(): string
    {
        $endDate = $this->endDate();
        if ($this->status !== 'finished' && ! is_null($endDate) && $endDate <= date('Y-m-d')) {
            return 'overdue';
        }
        return $this->status;
    }

    public function endDate(): Carbon|null
    {
        if (is_null($this->start_date)) {
            return null;
        }
        return Carbon::parse($this->start_date)->addDays($this->duration_days); // confirm Carbon API name
    }
}
