<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class DatabaseMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:monitor {--timeout=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the database connection is available';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeout = $this->option('timeout');
        $start = time();

        while (time() - $start < $timeout) {
            try {
                DB::connection()->getPdo();
                $this->info('Database connection successful.');
                return 0;
            } catch (Exception $e) {
                $this->error('Database connection failed: ' . $e->getMessage());
                sleep(1);
            }
        }

        $this->error('Database connection timed out after ' . $timeout . ' seconds.');
        return 1;
    }
} 