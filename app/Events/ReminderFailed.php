<?php

namespace App\Events;

use App\Models\Reminder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event triggered when a reminder fails to send.
 */
class ReminderFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The reminder that failed to send.
     *
     * @var Reminder
     */
    public Reminder $reminder;

    /**
     * The error message explaining why the reminder failed.
     *
     * @var string
     */
    public string $errorMessage;

    /**
     * Create a new event instance.
     *
     * @param Reminder $reminder
     * @param string $errorMessage
     */
    public function __construct(Reminder $reminder, string $errorMessage)
    {
        $this->reminder = $reminder;
        $this->errorMessage = $errorMessage;
    }
} 