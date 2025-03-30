<?php

namespace App\Events;

use App\Models\Reminder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event triggered when a reminder is successfully sent.
 */
class ReminderSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The reminder that was sent.
     *
     * @var Reminder
     */
    public Reminder $reminder;

    /**
     * Create a new event instance.
     *
     * @param Reminder $reminder
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }
} 