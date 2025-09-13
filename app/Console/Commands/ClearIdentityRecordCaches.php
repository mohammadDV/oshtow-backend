<?php

namespace App\Console\Commands;

use App\Filament\Resources\IdentityRecordResource;
use Illuminate\Console\Command;

class ClearIdentityRecordCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'identity-records:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all caches related to IdentityRecordResource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing IdentityRecord caches...');

        IdentityRecordResource::clearCaches();

        $this->info('✅ All IdentityRecord caches cleared successfully!');

        return Command::SUCCESS;
    }
}
