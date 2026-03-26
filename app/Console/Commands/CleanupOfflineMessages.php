<?php

namespace App\Console\Commands;

use App\Services\OfflineMessageService;
use Illuminate\Console\Command;

class CleanupOfflineMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rtm:cleanup-offline-messages {--days=7 : Number of days to keep offline messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old offline messages from cache and database';

    protected $offlineMessageService;

    /**
     * Create a new command instance.
     *
     * @param OfflineMessageService $offlineMessageService
     */
    public function __construct(OfflineMessageService $offlineMessageService)
    {
        parent::__construct();
        $this->offlineMessageService = $offlineMessageService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Cleaning up offline messages older than {$days} days...");

        try {
            // Get statistics before cleanup
            $statsBefore = $this->offlineMessageService->getStatistics();
            
            $this->info("Before cleanup:");
            $this->info("- Total offline messages: {$statsBefore['total_offline']}");
            $this->info("- Total delivered messages: {$statsBefore['total_delivered']}");

            // Perform cleanup
            $this->offlineMessageService->cleanupOldMessages($days);

            // Get statistics after cleanup
            $statsAfter = $this->offlineMessageService->getStatistics();
            
            $this->info("After cleanup:");
            $this->info("- Total offline messages: {$statsAfter['total_offline']}");
            $this->info("- Total delivered messages: {$statsAfter['total_delivered']}");

            $cleaned = $statsBefore['total_offline'] - $statsAfter['total_offline'];
            $this->info("Cleaned up {$cleaned} offline messages.");

            $this->info('Offline messages cleanup completed successfully.');

        } catch (\Exception $e) {
            $this->error('Failed to cleanup offline messages: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
