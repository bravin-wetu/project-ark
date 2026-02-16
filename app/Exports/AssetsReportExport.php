<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\Asset;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetsReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function collection()
    {
        return Asset::forProject($this->project->id)
            ->with(['hub', 'assignedUser'])
            ->orderBy('asset_tag')
            ->get()
            ->map(function ($asset) {
                return [
                    'asset_tag' => $asset->asset_tag,
                    'description' => $asset->description,
                    'category' => $asset->category ?? 'N/A',
                    'location' => $asset->hub->name ?? 'N/A',
                    'assigned_to' => $asset->assignedUser->name ?? 'Unassigned',
                    'acquisition_date' => $asset->acquisition_date 
                        ? Carbon::parse($asset->acquisition_date)->format('M d, Y') 
                        : 'N/A',
                    'status' => ucfirst(str_replace('_', ' ', $asset->status)),
                    'acquisition_cost' => $asset->acquisition_cost ?? 0,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Asset Tag',
            'Description',
            'Category',
            'Location',
            'Assigned To',
            'Acquisition Date',
            'Status',
            'Value (KES)',
        ];
    }

    public function title(): string
    {
        return 'Asset Register';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
