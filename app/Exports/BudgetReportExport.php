<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\BudgetLine;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class BudgetReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected Project $project;
    protected $startDate;
    protected $endDate;

    public function __construct(Project $project, $startDate = null, $endDate = null)
    {
        $this->project = $project;
        $this->startDate = $startDate ?? now()->startOfYear();
        $this->endDate = $endDate ?? now();
    }

    public function collection()
    {
        $budgetLines = BudgetLine::where('budgetable_type', Project::class)
            ->where('budgetable_id', $this->project->id)
            ->get()
            ->map(function ($line) {
                $committed = $line->purchaseOrderItems()
                    ->whereHas('purchaseOrder', fn($q) => $q->whereNotIn('status', ['cancelled', 'rejected']))
                    ->sum(DB::raw('quantity * unit_price'));
                
                $spent = $line->purchaseOrderItems()
                    ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['completed', 'closed']))
                    ->sum(DB::raw('quantity * unit_price'));
                
                $available = $line->allocated_amount - $committed;
                $utilization = $line->allocated_amount > 0 
                    ? round(($spent / $line->allocated_amount) * 100, 1) . '%' 
                    : '0%';
                
                return [
                    'code' => $line->code,
                    'name' => $line->name,
                    'allocated' => $line->allocated_amount,
                    'committed' => $committed,
                    'spent' => $spent,
                    'available' => $available,
                    'utilization' => $utilization,
                ];
            });

        return $budgetLines;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Budget Line',
            'Allocated',
            'Committed',
            'Spent',
            'Available',
            'Utilization %',
        ];
    }

    public function title(): string
    {
        return 'Budget Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
