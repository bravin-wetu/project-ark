<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Hub;
use App\Models\Department;
use App\Models\Donor;
use App\Models\BudgetCategory;
use App\Models\Project;
use App\Models\BudgetLine;
use App\Models\Supplier;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Rfq;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Roles
        $roles = [
            ['name' => 'System Administrator', 'slug' => Role::ADMIN, 'description' => 'Full system access', 'permissions' => ['*']],
            ['name' => 'Project Manager', 'slug' => Role::PROJECT_MANAGER, 'description' => 'Manages project budgets and approves requisitions'],
            ['name' => 'Department Head', 'slug' => Role::DEPARTMENT_HEAD, 'description' => 'Manages department budgets and approves internal spending'],
            ['name' => 'Finance Officer', 'slug' => Role::FINANCE_OFFICER, 'description' => 'Validates budget compliance and confirms expenditures'],
            ['name' => 'Procurement Officer', 'slug' => Role::PROCUREMENT_OFFICER, 'description' => 'Manages RFQs and Purchase Orders'],
            ['name' => 'Hub Manager', 'slug' => Role::HUB_MANAGER, 'description' => 'Confirms receipt of goods and asset custody'],
            ['name' => 'Executive Approver', 'slug' => Role::EXECUTIVE_APPROVER, 'description' => 'Approves high-value transactions'],
            ['name' => 'Staff', 'slug' => Role::STAFF, 'description' => 'Basic staff access - can create requisitions'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // Create Hubs
        $hubs = [
            ['name' => 'Nairobi Hub', 'code' => 'NRB', 'location' => 'Nairobi, Kenya'],
            ['name' => 'Mombasa Hub', 'code' => 'MSA', 'location' => 'Mombasa, Kenya'],
            ['name' => 'Kisumu Hub', 'code' => 'KSM', 'location' => 'Kisumu, Kenya'],
        ];

        foreach ($hubs as $hub) {
            Hub::create($hub);
        }

        // Create Departments
        $departments = [
            ['name' => 'Administration', 'code' => 'ADMIN', 'hub_id' => 1],
            ['name' => 'Human Resources', 'code' => 'HR', 'hub_id' => 1],
            ['name' => 'Information Technology', 'code' => 'IT', 'hub_id' => 1],
            ['name' => 'Finance', 'code' => 'FIN', 'hub_id' => 1],
            ['name' => 'Programs', 'code' => 'PROG', 'hub_id' => 1],
            ['name' => 'WASH', 'code' => 'WASH', 'hub_id' => 1, 'description' => 'Water, Sanitation & Hygiene'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create Donors
        $donors = [
            ['name' => 'UNICEF', 'code' => 'UNICEF', 'contact_person' => 'John Smith', 'email' => 'john@unicef.org'],
            ['name' => 'WHO', 'code' => 'WHO', 'contact_person' => 'Jane Doe', 'email' => 'jane@who.int'],
            ['name' => 'World Bank', 'code' => 'WB', 'contact_person' => 'Bob Wilson', 'email' => 'bob@worldbank.org'],
            ['name' => 'WFP', 'code' => 'WFP', 'contact_person' => 'Alice Brown', 'email' => 'alice@wfp.org'],
            ['name' => 'USAID', 'code' => 'USAID', 'contact_person' => 'Charlie Davis', 'email' => 'charlie@usaid.gov'],
        ];

        foreach ($donors as $donor) {
            Donor::create($donor);
        }

        // Create Budget Categories
        $categories = [
            ['name' => 'Personnel', 'code' => 'PERS', 'sort_order' => 1],
            ['name' => 'Equipment', 'code' => 'EQUIP', 'sort_order' => 2],
            ['name' => 'Supplies', 'code' => 'SUPP', 'sort_order' => 3],
            ['name' => 'Travel', 'code' => 'TRAV', 'sort_order' => 4],
            ['name' => 'Training', 'code' => 'TRAIN', 'sort_order' => 5],
            ['name' => 'Consultancy', 'code' => 'CONS', 'sort_order' => 6],
            ['name' => 'Operations', 'code' => 'OPS', 'sort_order' => 7],
            ['name' => 'Overhead', 'code' => 'OVER', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            BudgetCategory::create($cat);
        }

        // Create Admin User
        $adminUser = User::create([
            'name' => 'System Admin',
            'email' => 'admin@wetu.org',
            'password' => Hash::make('password'),
            'department_id' => 1,
            'hub_id' => 1,
            'employee_id' => 'EMP001',
            'job_title' => 'System Administrator',
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::where('slug', Role::ADMIN)->first();
        $adminUser->roles()->attach($adminRole);

        // Create sample users
        $sampleUsers = [
            [
                'name' => 'Project Manager',
                'email' => 'pm@wetu.org',
                'department_id' => 5,
                'hub_id' => 1,
                'employee_id' => 'EMP002',
                'job_title' => 'Senior Project Manager',
                'role' => Role::PROJECT_MANAGER,
            ],
            [
                'name' => 'Finance Officer',
                'email' => 'finance@wetu.org',
                'department_id' => 4,
                'hub_id' => 1,
                'employee_id' => 'EMP003',
                'job_title' => 'Finance Officer',
                'role' => Role::FINANCE_OFFICER,
            ],
            [
                'name' => 'Procurement Officer',
                'email' => 'procurement@wetu.org',
                'department_id' => 1,
                'hub_id' => 1,
                'employee_id' => 'EMP004',
                'job_title' => 'Procurement Officer',
                'role' => Role::PROCUREMENT_OFFICER,
            ],
        ];

        foreach ($sampleUsers as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            
            $user = User::create(array_merge($userData, [
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]));

            $role = Role::where('slug', $roleName)->first();
            $user->roles()->attach($role);
        }

        // Create Suppliers
        $suppliers = [
            [
                'name' => 'TechSupply Kenya Ltd',
                'code' => 'SUP-2026-0001',
                'contact_person' => 'James Mwangi',
                'email' => 'sales@techsupply.co.ke',
                'phone' => '+254 722 123456',
                'address' => 'Industrial Area, Nairobi',
                'categories' => ['IT Equipment', 'Electronics'],
                'status' => 'active',
                'rating' => 4.5,
            ],
            [
                'name' => 'Office World Ltd',
                'code' => 'SUP-2026-0002',
                'contact_person' => 'Sarah Wanjiru',
                'email' => 'info@officeworld.co.ke',
                'phone' => '+254 733 234567',
                'address' => 'Westlands, Nairobi',
                'categories' => ['Office Supplies', 'Furniture'],
                'status' => 'active',
                'rating' => 4.2,
            ],
            [
                'name' => 'Clean Water Solutions',
                'code' => 'SUP-2026-0003',
                'contact_person' => 'Peter Otieno',
                'email' => 'peter@cleanwater.co.ke',
                'phone' => '+254 711 345678',
                'address' => 'Kisumu Road, Kisumu',
                'categories' => ['WASH Equipment', 'Water Treatment'],
                'status' => 'active',
                'rating' => 4.8,
            ],
            [
                'name' => 'BuildRight Construction Supplies',
                'code' => 'SUP-2026-0004',
                'contact_person' => 'Grace Kimani',
                'email' => 'grace@buildright.co.ke',
                'phone' => '+254 700 456789',
                'address' => 'Mombasa Road, Nairobi',
                'categories' => ['Construction Materials', 'Tools'],
                'status' => 'active',
                'rating' => 4.0,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // Create a Sample Project
        $project = Project::create([
            'name' => 'WASH Community Program 2026',
            'code' => 'WASH-2026-001',
            'description' => 'Water, Sanitation and Hygiene program for rural communities in Western Kenya',
            'donor_id' => 1, // UNICEF
            'department_id' => 6, // WASH
            'project_manager_id' => 2, // PM user
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'total_budget' => 500000.00,
            'status' => 'active',
            'currency' => 'KES',
        ]);

        // Create Budget Lines for the project
        $budgetLines = [
            ['code' => 'BL-001', 'name' => 'Water Pumps & Equipment', 'budget_category_id' => 2, 'allocated' => 150000],
            ['code' => 'BL-002', 'name' => 'Sanitation Supplies', 'budget_category_id' => 3, 'allocated' => 100000],
            ['code' => 'BL-003', 'name' => 'Staff Training', 'budget_category_id' => 5, 'allocated' => 50000],
            ['code' => 'BL-004', 'name' => 'Vehicle & Travel', 'budget_category_id' => 4, 'allocated' => 75000],
            ['code' => 'BL-005', 'name' => 'Consultancy Services', 'budget_category_id' => 6, 'allocated' => 80000],
            ['code' => 'BL-006', 'name' => 'Administrative Overhead', 'budget_category_id' => 8, 'allocated' => 45000],
        ];

        foreach ($budgetLines as $line) {
            BudgetLine::create([
                'budgetable_type' => Project::class,
                'budgetable_id' => $project->id,
                'budget_category_id' => $line['budget_category_id'],
                'code' => $line['code'],
                'name' => $line['name'],
                'allocated' => $line['allocated'],
                'committed' => 0,
                'spent' => 0,
                'is_active' => true,
            ]);
        }

        // Create a Requisition
        $requisition = Requisition::create([
            'requisition_number' => 'REQ-2026-0001',
            'requisitionable_type' => Project::class,
            'requisitionable_id' => $project->id,
            'budget_line_id' => 1, // Water Pumps
            'title' => 'Water Pump Equipment for Kisumu Community',
            'description' => 'Procurement of hand pumps and spare parts for clean water access points.',
            'priority' => 'high',
            'required_date' => now()->addDays(30),
            'estimated_total' => 45000.00,
            'status' => 'approved',
            'requested_by' => 2,
            'approved_by' => 1,
            'approved_at' => now()->subDays(3),
        ]);

        // Add items to requisition
        $reqItems = [
            ['name' => 'Hand Water Pump - India Mark II', 'quantity' => 10, 'unit' => 'pcs', 'estimated_unit_price' => 3500],
            ['name' => 'Pump Cylinder Assembly', 'quantity' => 5, 'unit' => 'sets', 'estimated_unit_price' => 1500],
            ['name' => 'Pump Rod (3m)', 'quantity' => 20, 'unit' => 'pcs', 'estimated_unit_price' => 250],
        ];

        foreach ($reqItems as $item) {
            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'name' => $item['name'],
                'description' => 'Standard specification',
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'estimated_unit_price' => $item['estimated_unit_price'],
            ]);
        }

        // Create an RFQ
        $rfq = Rfq::create([
            'rfq_number' => 'RFQ-2026-0001',
            'rfqable_type' => Project::class,
            'rfqable_id' => $project->id,
            'requisition_id' => $requisition->id,
            'title' => 'RFQ for Water Pump Equipment',
            'description' => 'Request for quotations for hand pumps and spare parts',
            'closing_date' => now()->addDays(7),
            'min_quotes' => 3,
            'status' => 'awarded',
            'created_by' => 4, // Procurement
            'issue_date' => now()->subDays(10),
        ]);

        // Add suppliers to RFQ
        $rfq->suppliers()->attach([3, 4], ['invited_at' => now()->subDays(10)]);

        // Create Quotes
        $quote1 = Quote::create([
            'quote_number' => 'QUO-2026-0001',
            'rfq_id' => $rfq->id,
            'supplier_id' => 3, // Clean Water Solutions
            'subtotal' => 42000,
            'tax_amount' => 6720,
            'total_amount' => 48720,
            'valid_until' => now()->addDays(30),
            'delivery_days' => 14,
            'payment_terms' => 'net_30',
            'status' => 'selected',
            'submitted_at' => now()->subDays(5),
            'evaluation_score' => 92,
        ]);

        // Quote items
        $quoteItems1 = [
            ['requisition_item_id' => 1, 'name' => 'Hand Water Pump - India Mark II', 'quantity' => 10, 'unit' => 'pcs', 'unit_price' => 3200],
            ['requisition_item_id' => 2, 'name' => 'Pump Cylinder Assembly', 'quantity' => 5, 'unit' => 'sets', 'unit_price' => 1400],
            ['requisition_item_id' => 3, 'name' => 'Pump Rod (3m)', 'quantity' => 20, 'unit' => 'pcs', 'unit_price' => 200],
        ];

        foreach ($quoteItems1 as $item) {
            QuoteItem::create([
                'quote_id' => $quote1->id,
                'requisition_item_id' => $item['requisition_item_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        // Second quote (not selected)
        $quote2 = Quote::create([
            'quote_number' => 'QUO-2026-0002',
            'rfq_id' => $rfq->id,
            'supplier_id' => 4, // BuildRight
            'subtotal' => 47500,
            'tax_amount' => 7600,
            'total_amount' => 55100,
            'valid_until' => now()->addDays(30),
            'delivery_days' => 21,
            'payment_terms' => 'net_30',
            'status' => 'not_selected',
            'submitted_at' => now()->subDays(4),
            'evaluation_score' => 78,
        ]);

        $quoteItems2 = [
            ['requisition_item_id' => 1, 'name' => 'Hand Water Pump - India Mark II', 'quantity' => 10, 'unit' => 'pcs', 'unit_price' => 3700],
            ['requisition_item_id' => 2, 'name' => 'Pump Cylinder Assembly', 'quantity' => 5, 'unit' => 'sets', 'unit_price' => 1600],
            ['requisition_item_id' => 3, 'name' => 'Pump Rod (3m)', 'quantity' => 20, 'unit' => 'pcs', 'unit_price' => 275],
        ];

        foreach ($quoteItems2 as $item) {
            QuoteItem::create([
                'quote_id' => $quote2->id,
                'requisition_item_id' => $item['requisition_item_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        // Create Purchase Order
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'purchaseable_type' => Project::class,
            'purchaseable_id' => $project->id,
            'rfq_id' => $rfq->id,
            'quote_id' => $quote1->id,
            'requisition_id' => $requisition->id,
            'supplier_id' => 3,
            'budget_line_id' => 1,
            'delivery_hub_id' => 3, // Kisumu Hub
            'delivery_address' => 'WeTu Kisumu Hub, Kisumu Road, Kisumu',
            'expected_delivery_date' => now()->addDays(14),
            'payment_terms' => 'Net 30 days',
            'subtotal' => 42000,
            'tax_amount' => 6720,
            'shipping_amount' => 2500,
            'discount_amount' => 0,
            'total_amount' => 51220,
            'status' => 'sent',
            'created_by' => 4,
            'approved_by' => 1,
            'approved_at' => now()->subDays(1),
            'sent_at' => now(),
        ]);

        // PO Items
        $poItems = [
            ['quote_item_id' => 1, 'requisition_item_id' => 1, 'name' => 'Hand Water Pump - India Mark II', 'quantity' => 10, 'unit' => 'pcs', 'unit_price' => 3200],
            ['quote_item_id' => 2, 'requisition_item_id' => 2, 'name' => 'Pump Cylinder Assembly', 'quantity' => 5, 'unit' => 'sets', 'unit_price' => 1400],
            ['quote_item_id' => 3, 'requisition_item_id' => 3, 'name' => 'Pump Rod (3m)', 'quantity' => 20, 'unit' => 'pcs', 'unit_price' => 200],
        ];

        foreach ($poItems as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'quote_item_id' => $item['quote_item_id'],
                'requisition_item_id' => $item['requisition_item_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'received_quantity' => 0,
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
                'status' => 'pending',
            ]);
        }

        // Commit budget
        $budgetLine = BudgetLine::find(1);
        $budgetLine->committed = $po->total_amount;
        $budgetLine->save();

        // Create second project (draft status for testing)
        $project2 = Project::create([
            'name' => 'Emergency Response Fund 2026',
            'code' => 'ERF-2026-001',
            'description' => 'Emergency response and disaster relief fund for rapid deployment',
            'donor_id' => 4, // WFP
            'department_id' => 5, // Programs
            'project_manager_id' => 2,
            'start_date' => '2026-02-01',
            'end_date' => '2026-12-31',
            'total_budget' => 250000.00,
            'status' => 'active',
            'currency' => 'KES',
        ]);

        // Budget lines for second project
        $budgetLines2 = [
            ['code' => 'BL-001', 'name' => 'Emergency Supplies', 'budget_category_id' => 3, 'allocated' => 100000],
            ['code' => 'BL-002', 'name' => 'Transportation', 'budget_category_id' => 4, 'allocated' => 60000],
            ['code' => 'BL-003', 'name' => 'Field Staff', 'budget_category_id' => 1, 'allocated' => 50000],
            ['code' => 'BL-004', 'name' => 'Communications', 'budget_category_id' => 7, 'allocated' => 40000],
        ];

        foreach ($budgetLines2 as $line) {
            BudgetLine::create([
                'budgetable_type' => Project::class,
                'budgetable_id' => $project2->id,
                'budget_category_id' => $line['budget_category_id'],
                'code' => $line['code'],
                'name' => $line['name'],
                'allocated' => $line['allocated'],
                'committed' => 0,
                'spent' => 0,
                'is_active' => true,
            ]);
        }

        // Create a draft requisition for second project
        $requisition2 = Requisition::create([
            'requisition_number' => 'REQ-2026-0002',
            'requisitionable_type' => Project::class,
            'requisitionable_id' => $project2->id,
            'budget_line_id' => 7, // Emergency Supplies
            'title' => 'Emergency Relief Kits',
            'description' => 'Procurement of emergency relief kits for disaster response.',
            'priority' => 'urgent',
            'required_date' => now()->addDays(7),
            'estimated_total' => 25000.00,
            'status' => 'draft',
            'requested_by' => 2,
        ]);

        RequisitionItem::create([
            'requisition_id' => $requisition2->id,
            'name' => 'Emergency Relief Kit (Family)',
            'description' => 'Complete kit with blankets, water containers, first aid',
            'quantity' => 100,
            'unit' => 'kits',
            'estimated_unit_price' => 250,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('=== TEST ACCOUNTS ===');
        $this->command->info('Admin: admin@wetu.org / password');
        $this->command->info('PM: pm@wetu.org / password');
        $this->command->info('Finance: finance@wetu.org / password');
        $this->command->info('Procurement: procurement@wetu.org / password');
        $this->command->info('');
        $this->command->info('=== SAMPLE DATA ===');
        $this->command->info('2 Active Projects with budget lines');
        $this->command->info('4 Suppliers');
        $this->command->info('1 Approved Requisition with RFQ & Quotes');
        $this->command->info('1 Purchase Order (Sent status)');
        $this->command->info('1 Draft Requisition for testing');
    }
}
