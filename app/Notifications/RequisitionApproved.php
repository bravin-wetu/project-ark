<?php

namespace App\Notifications;

use App\Models\Requisition;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisitionApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Requisition $requisition,
        public User $approver
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('requisition_approved', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('requisition_approved', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Requisition {$this->requisition->requisition_number} Approved")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your requisition has been approved!")
            ->line("**Requisition:** {$this->requisition->requisition_number}")
            ->line("**Title:** {$this->requisition->title}")
            ->line("**Approved by:** {$this->approver->name}")
            ->action('View Requisition', $this->getActionUrl())
            ->line('The requisition can now proceed to the next step.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Requisition Approved',
            'message' => "Requisition {$this->requisition->requisition_number} was approved by {$this->approver->name}.",
            'icon' => 'check',
            'icon_color' => 'green',
            'action_url' => $this->getActionUrl(),
            'action_text' => 'View',
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
