<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $purchaseOrder->po_number }} - Purchase Order</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        .logo-sub {
            font-size: 10px;
            color: #6b7280;
            font-weight: normal;
        }
        .po-title {
            text-align: right;
        }
        .po-title h1 {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .po-number {
            font-size: 16px;
            color: #4b5563;
        }
        .po-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 8px;
        }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-sent { background: #dbeafe; color: #1e40af; }

        .info-section {
            display: flex;
            gap: 40px;
            margin-bottom: 25px;
        }
        .info-box {
            flex: 1;
        }
        .info-box h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        .info-box p {
            margin-bottom: 3px;
        }
        .info-box .label {
            color: #6b7280;
            font-size: 10px;
        }
        .info-box .value {
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th {
            background: #f3f4f6;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #d1d5db;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .item-name {
            font-weight: 500;
        }
        .item-desc {
            font-size: 10px;
            color: #6b7280;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            width: 250px;
        }
        .totals-table td {
            padding: 5px 8px;
            border: none;
        }
        .totals-table .total-row td {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }

        .terms-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .terms-section h3 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #374151;
        }
        .terms-section p {
            font-size: 10px;
            color: #6b7280;
            white-space: pre-wrap;
        }

        .signature-section {
            margin-top: 50px;
            display: flex;
            gap: 80px;
        }
        .signature-box {
            flex: 1;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 40px;
        }
        .signature-label {
            font-size: 10px;
            color: #6b7280;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #1f2937; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Purchase Order
        </button>
        <a href="{{ route('projects.purchase-orders.show', [$project, $purchaseOrder]) }}" style="padding: 8px 16px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 4px; margin-left: 8px;">
            Back to PO
        </a>
    </div>

    <div class="header">
        <div>
            <div class="logo">
                {{ config('app.name', 'WeTu') }}
                <div class="logo-sub">Procurement & Budget Control</div>
            </div>
            <p style="margin-top: 10px; font-size: 11px; color: #6b7280;">
                {{ $project->name }}
            </p>
        </div>
        <div class="po-title">
            <h1>PURCHASE ORDER</h1>
            <div class="po-number">{{ $purchaseOrder->po_number }}</div>
            <div class="po-status status-{{ $purchaseOrder->status }}">
                {{ strtoupper(str_replace('_', ' ', $purchaseOrder->status)) }}
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>Supplier</h3>
            <p class="value">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</p>
            <p>{{ $purchaseOrder->supplier->code ?? '' }}</p>
            @if($purchaseOrder->supplier->address)
                <p>{{ $purchaseOrder->supplier->address }}</p>
            @endif
            @if($purchaseOrder->supplier->phone)
                <p>Tel: {{ $purchaseOrder->supplier->phone }}</p>
            @endif
            @if($purchaseOrder->supplier->email)
                <p>Email: {{ $purchaseOrder->supplier->email }}</p>
            @endif
        </div>
        <div class="info-box">
            <h3>Ship To</h3>
            <p class="value">{{ $project->name }}</p>
            <p>{{ $purchaseOrder->delivery_address }}</p>
        </div>
        <div class="info-box">
            <h3>Order Details</h3>
            <p><span class="label">Date:</span> <span class="value">{{ $purchaseOrder->created_at->format('M d, Y') }}</span></p>
            <p><span class="label">Expected Delivery:</span> <span class="value">{{ $purchaseOrder->expected_delivery_date?->format('M d, Y') ?? 'TBD' }}</span></p>
            <p><span class="label">Payment Terms:</span> <span class="value">{{ str_replace('_', ' ', ucfirst($purchaseOrder->payment_terms ?? 'N/A')) }}</span></p>
            <p><span class="label">Shipping:</span> <span class="value">{{ ucfirst($purchaseOrder->shipping_method ?? 'Standard') }}</span></p>
            @if($purchaseOrder->supplier_reference)
                <p><span class="label">Supplier Ref:</span> <span class="value">{{ $purchaseOrder->supplier_reference }}</span></p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 45%">Description</th>
                <th style="width: 10%" class="text-right">Qty</th>
                <th style="width: 10%">Unit</th>
                <th style="width: 15%" class="text-right">Unit Price</th>
                <th style="width: 15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description)
                            <div class="item-desc">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-right">K{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">K{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">K{{ number_format($purchaseOrder->subtotal_amount, 2) }}</td>
            </tr>
            @if($purchaseOrder->shipping_amount > 0)
            <tr>
                <td>Shipping</td>
                <td class="text-right">K{{ number_format($purchaseOrder->shipping_amount, 2) }}</td>
            </tr>
            @endif
            @if($purchaseOrder->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">-K{{ number_format($purchaseOrder->discount_amount, 2) }}</td>
            </tr>
            @endif
            @if($purchaseOrder->tax_amount > 0)
            <tr>
                <td>Tax</td>
                <td class="text-right">K{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">K{{ number_format($purchaseOrder->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($purchaseOrder->terms_conditions)
        <div class="terms-section">
            <h3>Terms & Conditions</h3>
            <p>{{ $purchaseOrder->terms_conditions }}</p>
        </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-label">Authorized Signature</div>
                <p style="margin-top: 5px;">{{ $purchaseOrder->approver->name ?? '' }}</p>
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-label">Date</div>
                <p style="margin-top: 5px;">{{ $purchaseOrder->approved_at?->format('M d, Y') ?? '' }}</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. Generated on {{ now()->format('M d, Y H:i') }}</p>
        <p>{{ config('app.name', 'WeTu') }} - Integrated Procurement & Budget Control System</p>
    </div>
</body>
</html>
