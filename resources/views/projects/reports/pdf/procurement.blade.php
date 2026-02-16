<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Procurement Report - {{ $project->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1a1a1a;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: -0.5px;
        }
        .logo span {
            display: inline-block;
            background: #1a1a1a;
            color: white;
            padding: 4px 8px;
            margin-right: 5px;
        }
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 10px;
        }
        .report-info {
            font-size: 9pt;
            color: #666;
            margin-top: 5px;
        }
        .summary-box {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #1a1a1a;
        }
        .stats-grid {
            width: 100%;
        }
        .stats-grid td {
            border: none;
            padding: 8px 15px 8px 0;
            vertical-align: top;
        }
        .stat-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 14pt;
            font-weight: bold;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9pt;
        }
        table.data-table th {
            background: #1a1a1a;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
        }
        table.data-table th:last-child, 
        table.data-table td:last-child {
            text-align: right;
        }
        table.data-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e5e5;
        }
        table.data-table tr:nth-child(even) {
            background: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: 600;
        }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }
        .status-approved { background: #dbeafe; color: #1d4ed8; }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 25px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e5e5;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #e5e5e5;
            padding-top: 10px;
        }
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><span>W</span> WeTu</div>
        <div class="report-title">Procurement Spend Report</div>
        <div class="report-info">
            Project: {{ $project->name }}<br>
            Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}<br>
            Generated: {{ now()->format('M d, Y H:i') }}
        </div>
    </div>

    <div class="summary-box">
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-label">Total Requisitions</div>
                    <div class="stat-value">{{ $stats['total_requisitions'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Total RFQs</div>
                    <div class="stat-value">{{ $stats['total_rfqs'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Total POs</div>
                    <div class="stat-value">{{ $stats['total_pos'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Total Spend</div>
                    <div class="stat-value">KES {{ number_format($stats['total_spend'], 0) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Purchase Orders</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 80px;">PO Number</th>
                <th>Supplier</th>
                <th style="width: 80px;">Date</th>
                <th style="width: 70px;">Status</th>
                <th style="width: 100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $po)
            <tr>
                <td>{{ $po->po_number }}</td>
                <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                <td>{{ $po->created_at->format('M d, Y') }}</td>
                <td>
                    <span class="status status-{{ $po->status }}">{{ ucfirst($po->status) }}</span>
                </td>
                <td class="text-right">{{ number_format($po->total_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">No purchase orders found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($spendByCategory->isNotEmpty())
    <div class="section-title">Spend by Category</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Category</th>
                <th style="width: 120px;">Amount</th>
                <th style="width: 80px;">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($spendByCategory as $category)
            <tr>
                <td>{{ $category->category ?? 'Uncategorized' }}</td>
                <td class="text-right">KES {{ number_format($category->total, 2) }}</td>
                <td class="text-right">{{ $stats['total_spend'] > 0 ? number_format(($category->total / $stats['total_spend']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <div style="float: left;">WeTu Procurement & Budget Control System</div>
        <div style="float: right;">Page <span class="page-number"></span></div>
    </div>
</body>
</html>
