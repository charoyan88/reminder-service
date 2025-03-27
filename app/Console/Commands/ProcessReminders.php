<?php

namespace App\Console\Commands;

use App\Services\Interfaces\ReminderServiceInterface;
use App\Services\Mail\ReminderMailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:process {--limit=50 : Maximum number of reminders to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process reminders that are due to be sent';

    /**
     * @var ReminderServiceInterface
     */
    private ReminderServiceInterface $reminderService;

    /**
     * @var ReminderMailService
     */
    private ReminderMailService $mailService;

    /**
     * Create a new command instance.
     *
     * @param ReminderServiceInterface $reminderService
     * @param ReminderMailService $mailService
     */
    public function __construct(
        ReminderServiceInterface $reminderService,
        ReminderMailService $mailService
    ) {
        parent::__construct();
        $this->reminderService = $reminderService;
        $this->mailService = $mailService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting to process pending reminders...');
        
        try {
            $limit = (int) $this->option('limit');
            $this->info("Processing up to {$limit} reminders");
            
            $reminders = $this->reminderService->getPendingRemindersToSend();
            $count = $reminders->count();
            
            if ($count === 0) {
                $this->info('No pending reminders to send.');
                return 0;
            }
            
            $this->info("Found {$count} pending reminders. Processing...");
            $this->output->progressStart(min($count, $limit));
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($reminders->take($limit) as $reminder) {
                try {
                    // Check if the order is still active before sending
                    if (!$reminder->order->is_active || $reminder->order->isReplaced()) {
                        $this->reminderService->markReminderAsFailed(
                            $reminder, 
                            'Order is no longer active or has been replaced'
                        );
                        $failCount++;
                        $this->output->progressAdvance();
                        continue;
                    }
                    
                    // Send the email
                    $success = $this->mailService->sendReminderEmail($reminder);
                    
                    if ($success) {
                        // Mark as sent
                        $this->reminderService->markReminderAsSent($reminder);
                        $successCount++;
                    } else {
                        // Mark as failed
                        $this->reminderService->markReminderAsFailed(
                            $reminder, 
                            'Failed to send email'
                        );
                        $failCount++;
                    }
                } catch (Throwable $e) {
                    // Log the error and continue with next reminder
                    Log::error('Error processing reminder: ' . $e->getMessage(), [
                        'reminder_id' => $reminder->id,
                        'exception' => $e
                    ]);
                    
                    $this->reminderService->markReminderAsFailed(
                        $reminder, 
                        'Exception: ' . $e->getMessage()
                    );
                    $failCount++;
                }
                
                $this->output->progressAdvance();
            }
            
            $this->output->progressFinish();
            $this->info("Processed {$successCount} reminders successfully. Failed: {$failCount}");
            
            return 0;
        } catch (Throwable $e) {
            $this->error('Failed to process reminders: ' . $e->getMessage());
            Log::error('Failed to process reminders: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return 1;
        }
    }
} 