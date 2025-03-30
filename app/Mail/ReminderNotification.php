<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The reminder instance.
     *
     * @var Reminder
     */
    protected Reminder $reminder;

    /**
     * Create a new message instance.
     *
     * @param Reminder $reminder
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Use the content prepared at scheduling time rather than generating it now
        // This ensures consistency with what we scheduled
        return $this->subject($this->reminder->email_subject)
            ->html($this->reminder->email_content)
            ->with([
                'order' => $this->reminder->order,
                'reminder' => $this->reminder,
                'business' => $this->reminder->order->business,
                'user' => $this->reminder->order->user,
            ]);
    }
} 