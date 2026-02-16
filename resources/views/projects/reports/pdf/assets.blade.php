<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Register - {{ $project->name }}</title>
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
            font-size: 8pt;
        }
        table.data-table th {
            background: #1a1a1a;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
        }
        table.data-table td {
            padding: 6px;
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
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7pt;
            font-weight: 600;
        }
        .status-active { background: #dcfce7; color: #15803d; }
        .status-in_maintenance { background: #fef3c7; color: #b45309; }
        .status-disposed { background: #fee2e2; color: #b91c1c; }
        .status-transferred { background: #dbeafe; color: #1d4ed8; }
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
        <div class="report-title">Asset Register</div>
        <div class="report-info">
            Project: {{ $project->name }}<br>
            Generated: {{ now()->format('M d, Y H:i') }}
        </div>
    </div>

    <div class="summary-box">
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-label">Total Assets</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                </td>
                <td>
                    <div class="stat-label">In Maintenance</div>
                    <div class="stat-value">{{ $stats['in_maintenance'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Disposed</div>
                    <div class="stat-value">{{ $stats['disposed'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Total Value</div>
                    <div class="stat-value">KES {{ number_format($stats['total_value'], 0) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 80px;">Asset Tag</th>
                <th>Description</th>
                <th style="width: 70px;">Category</th>
                <th style="width: 70px;">Location</th>
                <th style="width: 80px;">Acquired</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 80px;">Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            <tr>
                <td>{{ $asset->asset_tag }}</td>
                <td>{{ Str::limit($asset->description, 40) }}</td>
                <td>{{ $asset->category ?? 'N/A' }}</td>
                <td>{{ $asset->hub->name ?? 'N/A' }}</td>
                <td>{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('M d, Y') : 'N/A' }}</td>
                <td>
                    <span class="status status-{{ $asset->status }}">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span>
                </td>
                <td class="text-right">{{ number_format($asset->acquisition_cost ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #999;">No assets found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div style="float: left;">WeTu Procurement & Budget Control System</div>
        <div style="float: right;">Page <span class="page-number"></span></div>
    </div>
</body>
</html>
