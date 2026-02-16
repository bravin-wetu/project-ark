<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProcurementReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
        return PurchaseOrder::where('purchaseable_type', Project::class)
            ->where('purchaseable_id', $this->project->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with('supplier')
            ->latest()
            ->get()
            ->map(function ($po) {
                return [
                    'po_number' => $po->po_number,
                    'supplier' => $po->supplier->name ?? 'N/A',
                    'date' => $po->created_at->format('M d, Y'),
                    'status' => ucfirst($po->status),
                    'amount' => $po->total_amount,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'PO Number',
            'Supplier',
            'Date',
            'Status',
            'Amount (KES)',
        ];
    }

    public function title(): string
    {
        return 'Procurement Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
