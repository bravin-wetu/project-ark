<?php

namespace App\Notifications;

use App\Models\Requisition;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisitionRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Requisition $requisition,
        public User $rejector,
        public ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('requisition_rejected', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('requisition_rejected', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Requisition {$this->requisition->requisition_number} Rejected")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your requisition has been rejected.")
            ->line("**Requisition:** {$this->requisition->requisition_number}")
            ->line("**Title:** {$this->requisition->title}")
            ->line("**Rejected by:** {$this->rejector->name}");

        if ($this->reason) {
            $mail->line("**Reason:** {$this->reason}");
        }

        return $mail
            ->action('View Requisition', $this->getActionUrl())
            ->line('You may revise and resubmit if appropriate.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Requisition Rejected',
            'message' => "Requisition {$this->requisition->requisition_number} was rejected by {$this->rejector->name}." . 
                        ($this->reason ? " Reason: {$this->reason}" : ''),
            'icon' => 'x',
            'icon_color' => 'red',
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
