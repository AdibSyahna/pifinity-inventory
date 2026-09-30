<?php

namespace App\Console\Commands;

use App\Models\Consignment;
use Illuminate\Console\Command;

class UpdateOverdueConsignments extends Command
{
    protected $signature = 'consignments:update-overdue';

    protected $description = 'Mark consignments as overdue once their end date has been reached or passed';

    public function handle(): void
    {
        $updated = Consignment::query()
            ->where('status', '!=', 'finished')
            ->whereNotNull('start_date')
            ->whereRaw('DATE_ADD(start_date, INTERVAL duration_days DAY) <= CURDATE()')
            ->update(['status' => 'overdue']);

        $this->info("Marked {$updated} consignment(s) as overdue.");
    }
}
