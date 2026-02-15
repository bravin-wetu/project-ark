<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectWorkspaceController;
use App\Http\Controllers\DepartmentBudgetController;
use App\Http\Controllers\DepartmentBudgetWorkspaceController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BudgetControlController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AnalyticsDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/fetch', [NotificationController::class, 'getNotifications'])->name('notifications.fetch');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::get('/notifications/settings', [NotificationController::class, 'settings'])->name('notifications.settings');
    Route::put('/notifications/settings', [NotificationController::class, 'updateSettings'])->name('notifications.settings.update');

    // Projects (Workspaces)
    Route::resource('projects', ProjectController::class);
    
    // Project Workspace routes (nested)
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        // Requisitions (full CRUD + workflow actions)
        Route::resource('requisitions', RequisitionController::class)->except(['index']);
        Route::get('/requisitions', [RequisitionController::class, 'index'])->name('requisitions.index');
        Route::post('/requisitions/{requisition}/submit', [RequisitionController::class, 'submit'])->name('requisitions.submit');
        Route::post('/requisitions/{requisition}/approve', [RequisitionController::class, 'approve'])->name('requisitions.approve');
        Route::post('/requisitions/{requisition}/reject', [RequisitionController::class, 'reject'])->name('requisitions.reject');
        Route::post('/requisitions/{requisition}/cancel', [RequisitionController::class, 'cancel'])->name('requisitions.cancel');
        
        // RFQs (full CRUD + workflow actions)
        Route::resource('rfqs', RfqController::class)->except(['index']);
        Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
        Route::post('/rfqs/{rfq}/send', [RfqController::class, 'send'])->name('rfqs.send');
        Route::get('/rfqs/{rfq}/analyze', [RfqController::class, 'analyze'])->name('rfqs.analyze');
        Route::post('/rfqs/{rfq}/award', [RfqController::class, 'award'])->name('rfqs.award');
        Route::post('/rfqs/{rfq}/cancel', [RfqController::class, 'cancel'])->name('rfqs.cancel');
        Route::post('/rfqs/{rfq}/add-quote', [RfqController::class, 'addQuote'])->name('rfqs.add-quote');
        
        // Purchase Orders (full CRUD + workflow actions)
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create-from-quote/{quote}', [PurchaseOrderController::class, 'createFromQuote'])->name('purchase-orders.create-from-quote');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::post('/purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');
        Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::post('/purchase-orders/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
        Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::post('/purchase-orders/{purchaseOrder}/acknowledge', [PurchaseOrderController::class, 'acknowledge'])->name('purchase-orders.acknowledge');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::post('/purchase-orders/{purchaseOrder}/close', [PurchaseOrderController::class, 'close'])->name('purchase-orders.close');
        Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
        
        // Goods Receipts (nested under purchase orders)
        Route::get('/purchase-orders/{purchaseOrder}/receipts/create', [GoodsReceiptController::class, 'create'])->name('purchase-orders.receipts.create');
        Route::post('/purchase-orders/{purchaseOrder}/receipts', [GoodsReceiptController::class, 'store'])->name('purchase-orders.receipts.store');
        Route::get('/purchase-orders/{purchaseOrder}/receipts/{receipt}', [GoodsReceiptController::class, 'show'])->name('purchase-orders.receipts.show');
        Route::post('/purchase-orders/{purchaseOrder}/receipts/{receipt}/confirm', [GoodsReceiptController::class, 'confirm'])->name('purchase-orders.receipts.confirm');
        Route::post('/purchase-orders/{purchaseOrder}/receipts/{receipt}/cancel', [GoodsReceiptController::class, 'cancel'])->name('purchase-orders.receipts.cancel');
        
        // Goods Receipts index (all receipts for project)
        Route::get('/receipts', [GoodsReceiptController::class, 'index'])->name('receipts.index');
        
        // Assets (Sprint 7)
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
        Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
        Route::get('/assets/{asset}/transfer', [AssetController::class, 'transferForm'])->name('assets.transfer-form');
        Route::post('/assets/{asset}/transfer', [AssetController::class, 'transfer'])->name('assets.transfer');
        Route::post('/assets/{asset}/maintenance', [AssetController::class, 'maintenance'])->name('assets.maintenance');
        Route::post('/assets/{asset}/maintenance/{maintenance}/complete', [AssetController::class, 'completeMaintenance'])->name('assets.complete-maintenance');
        Route::post('/assets/{asset}/dispose', [AssetController::class, 'dispose'])->name('assets.dispose');
        
        // Stock Items (Sprint 7)
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::get('/stock/{stockItem}', [StockController::class, 'show'])->name('stock.show');
        Route::get('/stock/{stockItem}/edit', [StockController::class, 'edit'])->name('stock.edit');
        Route::put('/stock/{stockItem}', [StockController::class, 'update'])->name('stock.update');
        Route::delete('/stock/{stockItem}', [StockController::class, 'destroy'])->name('stock.destroy');
        
        // Stock Batches
        Route::get('/stock/{stockItem}/batches', [StockController::class, 'batches'])->name('stock.batches');
        Route::get('/stock/{stockItem}/batches/create', [StockController::class, 'createBatch'])->name('stock.create-batch');
        Route::post('/stock/{stockItem}/batches', [StockController::class, 'storeBatch'])->name('stock.store-batch');
        
        // Stock Adjustments
        Route::get('/stock/{stockItem}/adjustments', [StockController::class, 'adjustments'])->name('stock.adjustments');
        Route::post('/stock/{stockItem}/adjustments', [StockController::class, 'storeAdjustment'])->name('stock.store-adjustment');
        
        // Stock Issues
        Route::get('/stock-issues', [StockController::class, 'issues'])->name('stock.issues');
        Route::get('/stock-issues/create', [StockController::class, 'createIssue'])->name('stock.create-issue');
        Route::post('/stock-issues', [StockController::class, 'storeIssue'])->name('stock.store-issue');
        Route::get('/stock-issues/{issue}', [StockController::class, 'showIssue'])->name('stock.show-issue');
        Route::post('/stock-issues/{issue}/approve', [StockController::class, 'approveIssue'])->name('stock.approve-issue');
        Route::post('/stock-issues/{issue}/complete', [StockController::class, 'completeIssue'])->name('stock.complete-issue');
        Route::post('/stock-issues/{issue}/reject', [StockController::class, 'rejectIssue'])->name('stock.reject-issue');
        
        // Other workspace routes
        Route::get('/quotes', [ProjectWorkspaceController::class, 'quotes'])->name('quotes.index');
        Route::get('/budget', [ProjectWorkspaceController::class, 'budget'])->name('budget.index');
        
        // Reports (Sprint 8)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/budget', [ReportController::class, 'budget'])->name('reports.budget');
        Route::get('/reports/procurement', [ReportController::class, 'procurement'])->name('reports.procurement');
        Route::get('/reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
        Route::get('/reports/assets', [ReportController::class, 'assets'])->name('reports.assets');
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        
        // Stock Movements
        Route::get('/stock-movements', [StockController::class, 'movements'])->name('stock.movements');
    });

    // Department Budgets (Workspaces)
    Route::resource('department-budgets', DepartmentBudgetController::class);
    
    // Department Budget Workspace routes (nested)
    Route::prefix('department-budgets/{department_budget}')->name('department-budgets.')->group(function () {
        Route::get('/requisitions', [DepartmentBudgetWorkspaceController::class, 'requisitions'])->name('requisitions.index');
        Route::get('/rfqs', [DepartmentBudgetWorkspaceController::class, 'rfqs'])->name('rfqs.index');
        Route::get('/quotes', [DepartmentBudgetWorkspaceController::class, 'quotes'])->name('quotes.index');
        Route::get('/purchase-orders', [DepartmentBudgetWorkspaceController::class, 'purchaseOrders'])->name('purchase-orders.index');
        Route::get('/receipts', [DepartmentBudgetWorkspaceController::class, 'receipts'])->name('receipts.index');
        Route::get('/assets', [DepartmentBudgetWorkspaceController::class, 'assets'])->name('assets.index');
        Route::get('/budget', [DepartmentBudgetWorkspaceController::class, 'budget'])->name('budget.index');
        Route::get('/reports', [DepartmentBudgetWorkspaceController::class, 'reports'])->name('reports.index');
    });

    // Budget Control (Sprint 10)
    Route::prefix('budget-control')->name('budget-control.')->group(function () {
        // Pending revisions
        Route::get('/pending-revisions', [BudgetControlController::class, 'pendingRevisions'])->name('pending-revisions');
        Route::get('/revisions/{revision}', [BudgetControlController::class, 'showRevision'])->name('show-revision');
        Route::post('/revisions/{revision}/approve', [BudgetControlController::class, 'approveRevision'])->name('approve-revision');
        Route::post('/revisions/{revision}/reject', [BudgetControlController::class, 'rejectRevision'])->name('reject-revision');
        Route::get('/revision-history', [BudgetControlController::class, 'getRevisionHistory'])->name('revision-history');
        
        // Budget line changes
        Route::post('/budget-lines/{budgetLine}/request-change', [BudgetControlController::class, 'requestChange'])->name('request-change');
        Route::post('/reallocate', [BudgetControlController::class, 'requestReallocation'])->name('request-reallocation');
        
        // Project budget controls
        Route::get('/projects/{project}', [BudgetControlController::class, 'projectIndex'])->name('project-index');
        Route::post('/projects/{project}/lock', [BudgetControlController::class, 'lockProject'])->name('lock-project');
        Route::post('/projects/{project}/unlock', [BudgetControlController::class, 'unlockProject'])->name('unlock-project');
        Route::put('/projects/{project}/thresholds', [BudgetControlController::class, 'updateProjectThresholds'])->name('project-thresholds');
        
        // Department budget controls
        Route::get('/department-budgets/{departmentBudget}', [BudgetControlController::class, 'departmentIndex'])->name('department-index');
        Route::post('/department-budgets/{departmentBudget}/lock', [BudgetControlController::class, 'lockDepartment'])->name('lock-department');
        Route::post('/department-budgets/{departmentBudget}/unlock', [BudgetControlController::class, 'unlockDepartment'])->name('unlock-department');
        Route::put('/department-budgets/{departmentBudget}/thresholds', [BudgetControlController::class, 'updateDepartmentThresholds'])->name('department-thresholds');
    });

    // Admin Routes (Sprint 11)
    Route::prefix('admin')->name('admin.')->group(function () {
        // User Management
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/export', [UserManagementController::class, 'export'])->name('users.export');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/invalidate-sessions', [UserManagementController::class, 'invalidateSessions'])->name('users.invalidate-sessions');
        Route::post('/users/bulk', [UserManagementController::class, 'bulkAction'])->name('users.bulk');

        // Role Management
        Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleManagementController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleManagementController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}', [RoleManagementController::class, 'show'])->name('roles.show');
        Route::get('/roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleManagementController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleManagementController::class, 'destroy'])->name('roles.destroy');
        Route::patch('/roles/{role}/toggle-status', [RoleManagementController::class, 'toggleStatus'])->name('roles.toggle-status');
        Route::post('/roles/{role}/clone', [RoleManagementController::class, 'clone'])->name('roles.clone');
        Route::post('/roles/bulk-assign-permission', [RoleManagementController::class, 'bulkAssignPermission'])->name('roles.bulk-assign-permission');
        Route::get('/api/permissions', [RoleManagementController::class, 'apiPermissions'])->name('api.permissions');

        // Audit Logs
        Route::get('/logs', [AuditLogController::class, 'dashboard'])->name('logs.dashboard');
        Route::get('/logs/audit', [AuditLogController::class, 'auditLogs'])->name('logs.audit');
        Route::get('/logs/audit/export', [AuditLogController::class, 'exportAuditLogs'])->name('logs.audit.export');
        Route::get('/logs/audit/{auditLog}', [AuditLogController::class, 'showAuditLog'])->name('logs.audit.show');
        Route::get('/logs/activity', [AuditLogController::class, 'activityLogs'])->name('logs.activity');
        Route::get('/logs/activity/export', [AuditLogController::class, 'exportActivityLogs'])->name('logs.activity.export');
        Route::get('/logs/activity/{activityLog}', [AuditLogController::class, 'showActivityLog'])->name('logs.activity.show');

        // System Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/general', [SettingsController::class, 'general'])->name('settings.general');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::get('/settings/currency', [SettingsController::class, 'currency'])->name('settings.currency');
        Route::post('/settings/currency', [SettingsController::class, 'updateCurrency'])->name('settings.currency.update');
        Route::get('/settings/fiscal', [SettingsController::class, 'fiscal'])->name('settings.fiscal');
        Route::post('/settings/fiscal', [SettingsController::class, 'updateFiscal'])->name('settings.fiscal.update');
        Route::get('/settings/procurement', [SettingsController::class, 'procurement'])->name('settings.procurement');
        Route::post('/settings/procurement', [SettingsController::class, 'updateProcurement'])->name('settings.procurement.update');
        Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
        Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
        Route::get('/settings/email', [SettingsController::class, 'email'])->name('settings.email');
        Route::post('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
        Route::post('/settings/email/test', [SettingsController::class, 'testEmail'])->name('settings.email.test');
        Route::get('/settings/approvals', [SettingsController::class, 'approvals'])->name('settings.approvals');
        Route::post('/settings/approvals', [SettingsController::class, 'updateApprovals'])->name('settings.approvals.update');
        Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/settings/seed-defaults', [SettingsController::class, 'seedDefaults'])->name('settings.seed-defaults');
    });

    // Analytics Dashboard Routes (Sprint 12)
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsDashboardController::class, 'index'])->name('index');
        Route::get('/budget-utilization', [AnalyticsDashboardController::class, 'budgetUtilization'])->name('budget');
        Route::get('/spending-analysis', [AnalyticsDashboardController::class, 'spendingAnalysis'])->name('spending');
        Route::get('/procurement', [AnalyticsDashboardController::class, 'procurementAnalytics'])->name('procurement');
        Route::get('/suppliers', [AnalyticsDashboardController::class, 'supplierAnalytics'])->name('suppliers');
        Route::get('/export', [AnalyticsDashboardController::class, 'export'])->name('export');
        Route::get('/chart-data', [AnalyticsDashboardController::class, 'chartData'])->name('chart-data');
        Route::get('/kpis', [AnalyticsDashboardController::class, 'kpisJson'])->name('kpis');
        Route::post('/clear-cache', [AnalyticsDashboardController::class, 'clearCache'])->name('clear-cache');
    });
});

require __DIR__.'/auth.php';
