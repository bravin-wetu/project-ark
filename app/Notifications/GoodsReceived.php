<?php

namespace App\Notifications;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoodsReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public GoodsReceipt $receipt,
        public PurchaseOrder $purchaseOrder
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('goods_received', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('goods_received', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $itemsReceived = $this->receipt->items->sum('quantity_received');
        
        return (new MailMessage)
            ->subject("Goods Received: {$this->receipt->grn_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Goods have been received for a purchase order.")
            ->line("**GRN:** {$this->receipt->grn_number}")
            ->line("**PO:** {$this->purchaseOrder->po_number}")
            ->line("**Supplier:** {$this->purchaseOrder->supplier->name}")
            ->line("**Items Received:** {$itemsReceived}")
            ->line("**PO Status:** " . ucfirst(str_replace('_', ' ', $this->purchaseOrder->fresh()->status)))
            ->action('View Receipt', $this->getActionUrl())
            ->line('Please verify the receipt and confirm if correct.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Goods Received',
            'message' => "GRN {$this->receipt->grn_number} received for PO {$this->purchaseOrder->po_number} from {$this->purchaseOrder->supplier->name}.",
            'icon' => 'goods-receipt',
            'icon_color' => 'green',
            'action_url' => $this->getActionUrl(),
            'action_text' => 'View',
            'receipt_id' => $this->receipt->id,
            'grn_number' => $this->receipt->grn_number,
            'purchase_order_id' => $this->purchaseOrder->id,
        ];
    }

    protected function getActionUrl(): string
    {
        $workspace = $this->purchaseOrder->orderable;
        $type = $workspace instanceof \App\Models\Project ? 'projects' : 'department-budgets';
        
        return route("{$type}.purchase-orders.receipts.show", [$workspace, $this->purchaseOrder, $this->receipt]);
    }
}
