<?php

namespace App\Console\Commands;

use App\Models\Companie;
use Illuminate\Console\Command;

class CheckExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiring';

    protected $description = 'Suspend companies whose trial period has expired';

    public function handle(): int
    {
        $count = Companie::suspendExpiredTrials();

        $this->info("Suspended {$count} expired trial(s).");

        return self::SUCCESS;
    }
}
