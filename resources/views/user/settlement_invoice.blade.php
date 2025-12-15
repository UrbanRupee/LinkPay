<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settlement Invoice - {{ $settlement->data2 ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f15a22;
        }
        .company-info h1 {
            color: #f15a22;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .company-info p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .invoice-details p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        .invoice-body {
            margin: 40px 0;
        }
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .billing-box {
            flex: 1;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            margin: 0 10px;
        }
        .billing-box h3 {
            color: #f15a22;
            font-size: 16px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .billing-box p {
            color: #333;
            font-size: 14px;
            margin: 5px 0;
            line-height: 1.6;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table thead {
            background: #f15a22;
            color: #fff;
        }
        .items-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .items-table tbody tr:hover {
            background: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin: 10px 0;
        }
        .total-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
            font-weight: 600;
            color: #333;
        }
        .total-value {
            width: 150px;
            text-align: right;
            font-weight: 600;
            color: #333;
        }
        .grand-total {
            font-size: 20px;
            color: #f15a22;
            border-top: 2px solid #f15a22;
            padding-top: 10px;
            margin-top: 10px;
        }
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }
        .status-success {
            background: #28a745;
            color: #fff;
        }
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        .print-button button {
            background: #f15a22;
            color: #fff;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .print-button button:hover {
            background: #d14a1a;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>{{ $siteName }}</h1>
                @if($siteAddress)
                    <p>{{ $siteAddress }}</p>
                @endif
                @if($siteEmail)
                    <p>Email: {{ $siteEmail }}</p>
                @endif
                @if($sitePhone)
                    <p>Phone: {{ $sitePhone }}</p>
                @endif
            </div>
            <div class="invoice-details">
                <h2>SETTLEMENT INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $settlement->data2 ?? 'N/A' }}</p>
                <p><strong>Date:</strong> {{ dformat($settlement->created_at, 'd-m-Y') }}</p>
                <p><strong>Time:</strong> {{ dformat($settlement->created_at, 'h:i:s A') }}</p>
                <p>
                    <strong>Status:</strong> 
                    <span class="status-badge status-success">Success</span>
                </p>
            </div>
        </div>

        <div class="invoice-body">
            <div class="billing-section">
                <div class="billing-box">
                    <h3>Merchant Details</h3>
                    <p><strong>Merchant ID:</strong> {{ $user->userid ?? 'N/A' }}</p>
                    <p><strong>Name:</strong> {{ $user->name ?? 'N/A' }}</p>
                    @if($user->email)
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                    @endif
                    @if($user->mobile)
                        <p><strong>Mobile:</strong> {{ $user->mobile }}</p>
                    @endif
                </div>
                @if($bankName || $accountNo || $ifscCode)
                <div class="billing-box">
                    <h3>Bank Details</h3>
                    @if($accountHolderName)
                        <p><strong>Account Holder:</strong> {{ strtoupper($accountHolderName) }}</p>
                    @endif
                    @if($bankName)
                        <p><strong>Bank Name:</strong> {{ $bankName }}</p>
                    @endif
                    @if($accountNo)
                        <p><strong>Account Number:</strong> {{ $accountNo }}</p>
                    @endif
                    @if($ifscCode)
                        <p><strong>IFSC Code:</strong> {{ strtoupper($ifscCode) }}</p>
                    @endif
                </div>
                @endif
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Settlement Amount</strong></td>
                        <td class="text-right">₹{{ number_format($grossAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Service Charge</td>
                        <td class="text-right">₹{{ number_format($taxAmount, 2) }}</td>
                    </tr>
                    @if(isset($holdAmount) && $holdAmount > 0)
                    <tr>
                        <td>Hold Amount (Chargeback Protection)</td>
                        <td class="text-right">₹{{ number_format($holdAmount, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="totals-section">
                <div class="total-row">
                    <div class="total-label">Settled Amount (Paid to Payout):</div>
                    <div class="total-value">₹{{ number_format($settledAmount, 2) }}</div>
                </div>
                @if(isset($holdAmount) && $holdAmount > 0)
                <div class="total-row" style="color: #f15a22;">
                    <div class="total-label">Hold Amount (In Hold Wallet):</div>
                    <div class="total-value">₹{{ number_format($holdAmount, 2) }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="invoice-footer">
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>Thank you for your business!</p>
        </div>

        <div class="print-button">
            <button onclick="window.print()">Print Invoice</button>
        </div>
    </div>

    <script>
        // Auto-print option (commented out - uncomment if needed)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settlement Invoice - {{ $settlement->data2 ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f15a22;
        }
        .company-info h1 {
            color: #f15a22;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .company-info p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .invoice-details p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        .invoice-body {
            margin: 40px 0;
        }
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .billing-box {
            flex: 1;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            margin: 0 10px;
        }
        .billing-box h3 {
            color: #f15a22;
            font-size: 16px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .billing-box p {
            color: #333;
            font-size: 14px;
            margin: 5px 0;
            line-height: 1.6;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table thead {
            background: #f15a22;
            color: #fff;
        }
        .items-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .items-table tbody tr:hover {
            background: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin: 10px 0;
        }
        .total-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
            font-weight: 600;
            color: #333;
        }
        .total-value {
            width: 150px;
            text-align: right;
            font-weight: 600;
            color: #333;
        }
        .grand-total {
            font-size: 20px;
            color: #f15a22;
            border-top: 2px solid #f15a22;
            padding-top: 10px;
            margin-top: 10px;
        }
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }
        .status-success {
            background: #28a745;
            color: #fff;
        }
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        .print-button button {
            background: #f15a22;
            color: #fff;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .print-button button:hover {
            background: #d14a1a;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>{{ $siteName }}</h1>
                @if($siteAddress)
                    <p>{{ $siteAddress }}</p>
                @endif
                @if($siteEmail)
                    <p>Email: {{ $siteEmail }}</p>
                @endif
                @if($sitePhone)
                    <p>Phone: {{ $sitePhone }}</p>
                @endif
            </div>
            <div class="invoice-details">
                <h2>SETTLEMENT INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $settlement->data2 ?? 'N/A' }}</p>
                <p><strong>Date:</strong> {{ dformat($settlement->created_at, 'd-m-Y') }}</p>
                <p><strong>Time:</strong> {{ dformat($settlement->created_at, 'h:i:s A') }}</p>
                <p>
                    <strong>Status:</strong> 
                    <span class="status-badge status-success">Success</span>
                </p>
            </div>
        </div>

        <div class="invoice-body">
            <div class="billing-section">
                <div class="billing-box">
                    <h3>Merchant Details</h3>
                    <p><strong>Merchant ID:</strong> {{ $user->userid ?? 'N/A' }}</p>
                    <p><strong>Name:</strong> {{ $user->name ?? 'N/A' }}</p>
                    @if($user->email)
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                    @endif
                    @if($user->mobile)
                        <p><strong>Mobile:</strong> {{ $user->mobile }}</p>
                    @endif
                </div>
                @if($bankName || $accountNo || $ifscCode)
                <div class="billing-box">
                    <h3>Bank Details</h3>
                    @if($accountHolderName)
                        <p><strong>Account Holder:</strong> {{ strtoupper($accountHolderName) }}</p>
                    @endif
                    @if($bankName)
                        <p><strong>Bank Name:</strong> {{ $bankName }}</p>
                    @endif
                    @if($accountNo)
                        <p><strong>Account Number:</strong> {{ $accountNo }}</p>
                    @endif
                    @if($ifscCode)
                        <p><strong>IFSC Code:</strong> {{ strtoupper($ifscCode) }}</p>
                    @endif
                </div>
                @endif
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Settlement Amount</strong></td>
                        <td class="text-right">₹{{ number_format($grossAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Service Charge</td>
                        <td class="text-right">₹{{ number_format($taxAmount, 2) }}</td>
                    </tr>
                    @if(isset($holdAmount) && $holdAmount > 0)
                    <tr>
                        <td>Hold Amount (Chargeback Protection)</td>
                        <td class="text-right">₹{{ number_format($holdAmount, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="totals-section">
                <div class="total-row">
                    <div class="total-label">Settled Amount (Paid to Payout):</div>
                    <div class="total-value">₹{{ number_format($settledAmount, 2) }}</div>
                </div>
                @if(isset($holdAmount) && $holdAmount > 0)
                <div class="total-row" style="color: #f15a22;">
                    <div class="total-label">Hold Amount (In Hold Wallet):</div>
                    <div class="total-value">₹{{ number_format($holdAmount, 2) }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="invoice-footer">
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>Thank you for your business!</p>
        </div>

        <div class="print-button">
            <button onclick="window.print()">Print Invoice</button>
        </div>
    </div>

    <script>
        // Auto-print option (commented out - uncomment if needed)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>

