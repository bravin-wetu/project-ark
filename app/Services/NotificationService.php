<?php

namespace App\Services;

use App\Models\Requisition;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\BudgetLine;
use App\Models\StockItem;
use App\Models\User;
use App\Notifications\RequisitionSubmitted;
use App\Notifications\RequisitionApproved;
use App\Notifications\RequisitionRejected;
use App\Notifications\PurchaseOrderApproved;
use App\Notifications\PurchaseOrderSent;
use App\Notifications\GoodsReceived;
use App\Notifications\BudgetThresholdAlert;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notify approvers that a requisition needs approval
     */
    public function notifyRequisitionSubmitted(Requisition $requisition, User $submitter): void
    {
        // Get users who can approve (approvers, admins, project managers)
        $approvers = $this->getApprovers($requisition);
        
        Notification::send($approvers, new RequisitionSubmitted($requisition, $submitter));
    }

    /**
     * Notify requisition creator that it was approved
     */
    public function notifyRequisitionApproved(Requisition $requisition, User $approver): void
    {
        if ($requisition->creator) {
            $requisition->creator->notify(new RequisitionApproved($requisition, $approver));
        }
    }

    /**
     * Notify requisition creator that it was rejected
     */
    public function notifyRequisitionRejected(Requisition $requisition, User $rejector, ?string $reason = null): void
    {
        if ($requisition->creator) {
            $requisition->creator->notify(new RequisitionRejected($requisition, $rejector, $reason));
        }
    }

    /**
     * Notify relevant users when a PO is approved
     */
    public function notifyPurchaseOrderApproved(PurchaseOrder $purchaseOrder, User $approver): void
    {
        // Notify the PO creator
        if ($purchaseOrder->creator) {
            $purchaseOrder->creator->notify(new PurchaseOrderApproved($purchaseOrder, $approver));
        }

        // Notify procurement staff who may need to send it
        $procurementStaff = $this->getProcurementStaff($purchaseOrder);
        Notification::send($procurementStaff, new PurchaseOrderApproved($purchaseOrder, $approver));
    }

    /**
     * Notify relevant users when a PO is sent to supplier
     */
    public function notifyPurchaseOrderSent(PurchaseOrder $purchaseOrder): void
    {
        // Notify the PO creator and project manager
        $recipients = collect();
        
        if ($purchaseOrder->creator) {
            $recipients->push($purchaseOrder->creator);
        }

        $workspace = $purchaseOrder->orderable;
        if ($workspace instanceof \App\Models\Project && $workspace->projectManager) {
            $recipients->push($workspace->projectManager);
        }

        Notification::send($recipients->unique('id'), new PurchaseOrderSent($purchaseOrder));
    }

    /**
     * Notify relevant users when goods are received
     */
    public function notifyGoodsReceived(GoodsReceipt $receipt, PurchaseOrder $purchaseOrder): void
    {
        $recipients = collect();

        // Notify PO creator
        if ($purchaseOrder->creator) {
            $recipients->push($purchaseOrder->creator);
        }

        // Notify project manager
        $workspace = $purchaseOrder->orderable;
        if ($workspace instanceof \App\Models\Project && $workspace->projectManager) {
            $recipients->push($workspace->projectManager);
        }

        Notification::send($recipients->unique('id'), new GoodsReceived($receipt, $purchaseOrder));
    }

    /**
     * Check budget utilization and send alerts if threshold exceeded
     */
    public function checkBudgetThreshold(BudgetLine $budgetLine): void
    {
        $utilization = $budgetLine->getUtilizationPercent();
        
        // Alert at 80% and 100%
        if ($utilization >= 80) {
            $workspace = $budgetLine->budgetable;
            $workspaceName = $workspace instanceof \App\Models\Project 
                ? $workspace->name 
                : $workspace->name;

            // Get project manager and finance users
            $recipients = $this->getBudgetAlertRecipients($budgetLine);

            Notification::send($recipients, new BudgetThresholdAlert($budgetLine, $utilization, $workspaceName));
        }
    }

    /**
     * Send low stock alert
     */
    public function sendLowStockAlert(StockItem $stockItem): void
    {
        if ($stockItem->current_quantity > $stockItem->reorder_level) {
            return; // Not actually low
        }

        $workspace = $stockItem->stockable;
        $workspaceName = $workspace instanceof \App\Models\Project 
            ? $workspace->name 
            : $workspace->name;

        // Get inventory managers and project managers
        $recipients = $this->getStockAlertRecipients($stockItem);

        Notification::send($recipients, new LowStockAlert($stockItem, $workspaceName));
    }

    /**
     * Get users who can approve requisitions for a workspace
     */
    protected function getApprovers(Requisition $requisition): \Illuminate\Support\Collection
    {
        $workspace = $requisition->requisitionable;
        $approvers = collect();

        // Add project manager if workspace is a project
        if ($workspace instanceof \App\Models\Project && $workspace->projectManager) {
            $approvers->push($workspace->projectManager);
        }

        // Add users with approver role
        $approverRole = \App\Models\Role::where('slug', 'approver')->first();
        if ($approverRole) {
            $approvers = $approvers->merge($approverRole->users);
        }

        // Add admins
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $approvers = $approvers->merge($adminRole->users);
        }

        return $approvers->unique('id');
    }

    /**
     * Get procurement staff for a workspace
     */
    protected function getProcurementStaff(PurchaseOrder $purchaseOrder): \Illuminate\Support\Collection
    {
        $procurementRole = \App\Models\Role::where('slug', 'procurement-officer')->first();
        
        return $procurementRole ? $procurementRole->users : collect();
    }

    /**
     * Get recipients for budget alerts
     */
    protected function getBudgetAlertRecipients(BudgetLine $budgetLine): \Illuminate\Support\Collection
    {
        $recipients = collect();
        $workspace = $budgetLine->budgetable;

        // Add project manager
        if ($workspace instanceof \App\Models\Project && $workspace->projectManager) {
            $recipients->push($workspace->projectManager);
        }

        // Add finance users
        $financeRole = \App\Models\Role::where('slug', 'finance-officer')->first();
        if ($financeRole) {
            $recipients = $recipients->merge($financeRole->users);
        }

        // Add admins
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $recipients = $recipients->merge($adminRole->users);
        }

        return $recipients->unique('id');
    }

    /**
     * Get recipients for stock alerts
     */
    protected function getStockAlertRecipients(StockItem $stockItem): \Illuminate\Support\Collection
    {
        $recipients = collect();
        $workspace = $stockItem->stockable;

        // Add project manager
        if ($workspace instanceof \App\Models\Project && $workspace->projectManager) {
            $recipients->push($workspace->projectManager);
        }

        // Add inventory managers
        $inventoryRole = \App\Models\Role::where('slug', 'inventory-manager')->first();
        if ($inventoryRole) {
            $recipients = $recipients->merge($inventoryRole->users);
        }

        // Add procurement users
        $procurementRole = \App\Models\Role::where('slug', 'procurement-officer')->first();
        if ($procurementRole) {
            $recipients = $recipients->merge($procurementRole->users);
        }

        return $recipients->unique('id');
    }
}
