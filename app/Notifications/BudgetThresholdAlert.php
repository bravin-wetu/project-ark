<?php

namespace App\Notifications;

use App\Models\BudgetLine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetThresholdAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BudgetLine $budgetLine,
        public float $utilizationPercent,
        public string $workspaceName
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('budget_threshold', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('budget_threshold', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->utilizationPercent >= 100 ? 'exceeded' : 'approaching limit';
        $subject = $this->utilizationPercent >= 100 
            ? "Budget Exceeded: {$this->budgetLine->name}"
            : "Budget Alert: {$this->budgetLine->name}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("A budget line has {$status}.")
            ->line("**Workspace:** {$this->workspaceName}")
            ->line("**Budget Line:** {$this->budgetLine->name} ({$this->budgetLine->code})")
            ->line("**Allocated:** KES " . number_format($this->budgetLine->allocated_amount, 2))
            ->line("**Utilization:** " . number_format($this->utilizationPercent, 1) . "%")
            ->action('View Budget', $this->getActionUrl())
            ->line('Please review and take appropriate action.');
    }

    public function toArray(object $notifiable): array
    {
        $title = $this->utilizationPercent >= 100 
            ? 'Budget Exceeded' 
            : 'Budget Threshold Warning';
        
        $iconColor = $this->utilizationPercent >= 100 ? 'red' : 'amber';

        return [
            'title' => $title,
            'message' => "Budget line '{$this->budgetLine->name}' is at " . number_format($this->utilizationPercent, 1) . "% utilization.",
            'icon' => 'warning',
            'icon_color' => $iconColor,
            'action_url' => $this->getActionUrl(),
            'action_text' => 'View Budget',
            'budget_line_id' => $this->budgetLine->id,
            'utilization_percent' => $this->utilizationPercent,
        ];
    }

    protected function getActionUrl(): string
    {
        $workspace = $this->budgetLine->budgetable;
        $type = $workspace instanceof \App\Models\Project ? 'projects' : 'department-budgets';
        
        return route("{$type}.budget.index", $workspace);
    }
}
