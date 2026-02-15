<?php

namespace App\Notifications;

use App\Models\Requisition;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisitionSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Requisition $requisition,
        public User $submitter
    ) {}

    /**
     * Determine which channels to use based on user preferences
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('requisition_submitted', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('requisition_submitted', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation
     */
    public function toMail(object $notifiable): MailMessage
    {
        $workspace = $this->requisition->requisitionable;
        $workspaceType = class_basename($workspace);

        return (new MailMessage)
            ->subject("Requisition {$this->requisition->requisition_number} Submitted for Approval")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new requisition has been submitted for your approval.")
            ->line("**Requisition:** {$this->requisition->requisition_number}")
            ->line("**Title:** {$this->requisition->title}")
            ->line("**Submitted by:** {$this->submitter->name}")
            ->line("**Amount:** KES " . number_format($this->requisition->total_amount, 2))
            ->action('Review Requisition', $this->getActionUrl())
            ->line('Please review and take appropriate action.');
    }

    /**
     * Get the database representation
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Requisition Pending Approval',
            'message' => "{$this->submitter->name} submitted requisition {$this->requisition->requisition_number} for approval.",
            'icon' => 'requisition',
            'icon_color' => 'blue',
            'action_url' => $this->getActionUrl(),
            'action_text' => 'Review',
            'requisition_id' => $this->requisition->id,
            'requisition_number' => $this->requisition->requisition_number,
        ];
    }

    protected function getActionUrl(): string
    {
        $workspace = $this->requisition->requisitionable;
        $type = $workspace instanceof \App\Models\Project ? 'projects' : 'department-budgets';
        
        return route("{$type}.requisitions.show", [$workspace, $this->requisition]);
    }
}
