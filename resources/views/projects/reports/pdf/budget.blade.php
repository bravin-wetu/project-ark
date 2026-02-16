<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget Utilization Report - {{ $project->name }}</title>
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
        .summary-row {
            display: flex;
            margin-bottom: 8px;
        }
        .summary-label {
            font-weight: bold;
            width: 150px;
        }
        .summary-value {
            color: #1a1a1a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9pt;
        }
        th {
            background: #1a1a1a;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
        }
        th:last-child, td:last-child {
            text-align: right;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e5e5;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .totals-row {
            font-weight: bold;
            background: #f0f0f0 !important;
            border-top: 2px solid #1a1a1a;
        }
        .progress-bar {
            width: 60px;
            height: 8px;
            background: #e5e5e5;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }
        .progress-fill {
            height: 100%;
            background: #1a1a1a;
            border-radius: 4px;
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
        .warning {
            color: #b91c1c;
        }
        .success {
            color: #15803d;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><span>W</span> WeTu</div>
        <div class="report-title">Budget Utilization Report</div>
        <div class="report-info">
            Project: {{ $project->name }}<br>
            Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}<br>
            Generated: {{ now()->format('M d, Y H:i') }}
        </div>
    </div>

    <div class="summary-box">
        <table style="margin: 0; width: 100%;">
            <tr>
                <td style="border: none; padding: 5px 0;"><strong>Total Budget:</strong></td>
                <td style="border: none; padding: 5px 0; text-align: right;">KES {{ number_format($totals['allocated'], 2) }}</td>
                <td style="border: none; padding: 5px 0; width: 50px;"></td>
                <td style="border: none; padding: 5px 0;"><strong>Total Committed:</strong></td>
                <td style="border: none; padding: 5px 0; text-align: right;">KES {{ number_format($totals['committed'], 2) }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px 0;"><strong>Total Spent:</strong></td>
                <td style="border: none; padding: 5px 0; text-align: right;">KES {{ number_format($totals['spent'], 2) }}</td>
                <td style="border: none; padding: 5px 0;"></td>
                <td style="border: none; padding: 5px 0;"><strong>Available:</strong></td>
                <td style="border: none; padding: 5px 0; text-align: right;">KES {{ number_format($totals['available'], 2) }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px 0;"><strong>Utilization Rate:</strong></td>
                <td style="border: none; padding: 5px 0; text-align: right;">{{ $totals['allocated'] > 0 ? number_format(($totals['spent'] / $totals['allocated']) * 100, 1) : 0 }}%</td>
                <td style="border: none; padding: 5px 0;"></td>
                <td style="border: none; padding: 5px 0;"></td>
                <td style="border: none; padding: 5px 0;"></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60px;">Code</th>
                <th>Budget Line</th>
                <th style="width: 100px;">Allocated</th>
                <th style="width: 100px;">Committed</th>
                <th style="width: 100px;">Spent</th>
                <th style="width: 100px;">Available</th>
                <th style="width: 70px;">Used %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgetLines as $line)
            <tr>
                <td>{{ $line->code }}</td>
                <td>{{ $line->name }}</td>
                <td class="text-right">{{ number_format($line->allocated_amount, 2) }}</td>
                <td class="text-right">{{ number_format($line->committed ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($line->spent ?? 0, 2) }}</td>
                <td class="text-right {{ ($line->allocated_amount - ($line->committed ?? 0)) < 0 ? 'warning' : '' }}">
                    {{ number_format($line->allocated_amount - ($line->committed ?? 0), 2) }}
                </td>
                <td class="text-right">
                    {{ $line->allocated_amount > 0 ? number_format((($line->spent ?? 0) / $line->allocated_amount) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="2">TOTALS</td>
                <td class="text-right">{{ number_format($totals['allocated'], 2) }}</td>
                <td class="text-right">{{ number_format($totals['committed'], 2) }}</td>
                <td class="text-right">{{ number_format($totals['spent'], 2) }}</td>
                <td class="text-right">{{ number_format($totals['available'], 2) }}</td>
                <td class="text-right">{{ $totals['allocated'] > 0 ? number_format(($totals['spent'] / $totals['allocated']) * 100, 1) : 0 }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div style="float: left;">WeTu Procurement & Budget Control System</div>
        <div style="float: right;">Page <span class="page-number"></span></div>
    </div>
</body>
</html>
