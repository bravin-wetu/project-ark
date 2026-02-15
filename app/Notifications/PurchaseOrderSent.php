<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderSent extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseOrder $purchaseOrder
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('po_sent', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('po_sent', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Purchase Order {$this->purchaseOrder->po_number} Sent to Supplier")
            ->greeting("Hello {$notifiable->name},")
            ->line("A purchase order has been dispatched to the supplier.")
            ->line("**PO Number:** {$this->purchaseOrder->po_number}")
            ->line("**Supplier:** {$this->purchaseOrder->supplier->name}")
            ->line("**Amount:** KES " . number_format($this->purchaseOrder->total_amount, 2))
            ->line("**Expected Delivery:** " . ($this->purchaseOrder->expected_delivery_date 
                ? $this->purchaseOrder->expected_delivery_date->format('M d, Y') 
                : 'Not specified'))
            ->action('View Purchase Order', $this->getActionUrl())
            ->line('Awaiting supplier acknowledgment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Purchase Order Sent',
            'message' => "PO {$this->purchaseOrder->po_number} has been sent to {$this->purchaseOrder->supplier->name}.",
            'icon' => 'purchase-order',
            'icon_color' => 'blue',
            'action_url' => $this->getActionUrl(),
            'action_text' => 'View',
            'purchase_order_id' => $this->purchaseOrder->id,
            'po_number' => $this->purchaseOrder->po_number,
        ];
    }

    protected function getActionUrl(): string
    {
        $workspace = $this->purchaseOrder->orderable;
        $type = $workspace instanceof \App\Models\Project ? 'projects' : 'department-budgets';
        
        return route("{$type}.purchase-orders.show", [$workspace, $this->purchaseOrder]);
    }
}
