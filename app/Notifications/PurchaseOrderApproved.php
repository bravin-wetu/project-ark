<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public User $approver
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $settings = $notifiable->getNotificationSettings();

        if ($settings->isEnabled('po_approved', 'app')) {
            $channels[] = 'database';
        }

        if ($settings->isEnabled('po_approved', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Purchase Order {$this->purchaseOrder->po_number} Approved")
            ->greeting("Hello {$notifiable->name},")
            ->line("A purchase order has been approved and is ready to send to the supplier.")
            ->line("**PO Number:** {$this->purchaseOrder->po_number}")
            ->line("**Supplier:** {$this->purchaseOrder->supplier->name}")
            ->line("**Amount:** KES " . number_format($this->purchaseOrder->total_amount, 2))
            ->line("**Approved by:** {$this->approver->name}")
            ->action('View Purchase Order', $this->getActionUrl())
            ->line('The PO can now be sent to the supplier.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Purchase Order Approved',
            'message' => "PO {$this->purchaseOrder->po_number} for {$this->purchaseOrder->supplier->name} approved by {$this->approver->name}.",
            'icon' => 'check',
            'icon_color' => 'green',
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
