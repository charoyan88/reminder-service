<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;

class ProcessReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all pending reminders that are due';

    /**
     * The email service instance.
     */
    protected EmailService $emailService;

    /**
     * Create a new command instance.
     */
    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process pending reminders...');
        
        $results = $this->emailService->sendPendingReminders();
        
        $this->info('Processed ' . $results['total'] . ' pending reminders.');
        $this->info('- Sent: ' . $results['sent']);
        $this->info('- Failed: ' . $results['failed']);
        $this->info('- Cancelled: ' . $results['cancelled']);
        
        return 0;
    }
} 