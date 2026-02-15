<?php

namespace App\Notifications;

use App\Models\StockItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StockItem $stockItem,
        public string $workspaceName
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('stock_low', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('stock_low', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Low Stock Alert: {$this->stockItem->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A stock item is running low and may need reordering.")
            ->line("**Workspace:** {$this->workspaceName}")
            ->line("**Item:** {$this->stockItem->name}")
            ->line("**SKU:** " . ($this->stockItem->sku ?? 'N/A'))
            ->line("**Current Quantity:** {$this->stockItem->current_quantity} {$this->stockItem->unit}")
            ->line("**Reorder Level:** {$this->stockItem->reorder_level} {$this->stockItem->unit}")
            ->action('View Stock Item', $this->getActionUrl())
            ->line('Please review and initiate restocking if necessary.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Low Stock Alert',
            'message' => "{$this->stockItem->name} is below reorder level ({$this->stockItem->current_quantity}/{$this->stockItem->reorder_level} {$this->stockItem->unit}).",
            'icon' => 'stock',
            'icon_color' => 'amber',
            'action_url' => $this->getActionUrl(),
            'action_text' => 'View',
            'stock_item_id' => $this->stockItem->id,
            'current_quantity' => $this->stockItem->current_quantity,
            'reorder_level' => $this->stockItem->reorder_level,
        ];
    }

    protected function getActionUrl(): string
    {
        $workspace = $this->stockItem->stockable;
        $type = $workspace instanceof \App\Models\Project ? 'projects' : 'department-budgets';
        
        return route("{$type}.stock.show", [$workspace, $this->stockItem]);
    }
}
