<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment {{ isset($status) && $status == 1 ? 'Receipt' : 'Status' }} - DhanKubera</title>
    <style>
        :root {
            --primary-dark: #0f172a;
            --primary: #1d4ed8;
            --success-bg: #ecfdf5;
            --success-text: #047857;
            --pending-bg: #fffbeb;
            --pending-text: #b45309;
            --failed-bg: #fef2f2;
            --failed-text: #b91c1c;
            --card-bg: rgba(255, 255, 255, 0.9);
            --border-color: #e5e7eb;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .receipt-wrapper {
            width: 100%;
            max-width: 720px;
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.15);
            overflow: hidden;
        }

        .receipt-header {
            padding: 2.5rem 2rem 2rem;
            background: #0f172a;
            color: white;
            position: relative;
        }

        .receipt-header h1 {
            font-size: 1.25rem;
            letter-spacing: 3px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.35rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .amount-display {
            font-size: 3rem;
            font-weight: 700;
            margin-top: 1.5rem;
        }

        .receipt-body {
            padding: 2rem;
        }

        .grid-two {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .info-card {
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem;
            background: white;
        }

        .info-card h3 {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.85rem;
        }

        .info-label {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .info-value {
            font-weight: 600;
            color: #111827;
            font-size: 0.95rem;
            text-align: right;
            max-width: 65%;
            word-break: break-word;
        }

        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .detail-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 0.3rem 0.75rem;
            font-size: 0.8rem;
            color: #1f2937;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 2rem;
        }

        .btn {
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary {
            background: #1d4ed8;
            color: white;
        }

        .btn-outline {
            background: white;
            border: 1px solid #cbd5f5;
            color: #1d4ed8;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(29, 78, 216, 0.15);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state h2 {
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--muted);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-wrapper {
                box-shadow: none;
                border-radius: 0;
            }
            .actions {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    @php
        $statusCode = $status ?? null;
        $statusConfig = [
            1 => ['label' => 'Payment Successful', 'bg' => 'var(--success-bg)', 'color' => 'var(--success-text)', 'icon' => '✔'],
            0 => ['label' => 'Payment Pending', 'bg' => 'var(--pending-bg)', 'color' => 'var(--pending-text)', 'icon' => '⏳'],
            2 => ['label' => 'Payment Failed', 'bg' => 'var(--failed-bg)', 'color' => 'var(--failed-text)', 'icon' => '✖'],
        ];
        $config = $statusConfig[$statusCode] ?? ['label' => 'Payment Status', 'bg' => '#f3f4f6', 'color' => '#1f2937', 'icon' => 'ℹ'];
        $redirectUrl = $redirectUrl ?? null;
    @endphp

    <div class="receipt-wrapper">
        @if(!$found)
            <div class="empty-state">
                <h2>Transaction Not Found</h2>
                <p>We could not locate a payment associated with transaction ID <strong>{{ $txnId }}</strong>.</p>
        </div>
        @else
            <div class="receipt-header">
                <h1>DHANKUBERA</h1>
                <div class="status-chip" style="background: {{ $config['bg'] }}; color: {{ $config['color'] }};">
                    <span>{{ $config['icon'] }}</span>
                    <span>{{ $config['label'] }}</span>
                </div>
                <div class="amount-display">₹{{ number_format($amount ?? 0, 2) }}</div>
            </div>

            <div class="receipt-body">
                <div class="grid-two" style="margin-bottom: 1.5rem;">
                    <div class="info-card">
                        <h3>Transaction Summary</h3>
                        <div class="info-row">
                            <div class="info-label">Transaction ID</div>
                            <div class="info-value">{{ $txnId }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Gateway Reference</div>
                            <div class="info-value">{{ $gatewayMeta['gateway_txn'] ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">UTR / Reference</div>
                            <div class="info-value">{{ $utr ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date & Time</div>
                            <div class="info-value">{{ optional($createdAt)->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>Payment Method</h3>
                        <div class="info-row">
                            <div class="info-label">Mode</div>
                            <div class="info-value">{{ $gatewayMeta['mode'] ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Card Category</div>
                            <div class="info-value">{{ $gatewayMeta['card_category'] ?? '—' }}</div>
                        </div>
                        <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                            <div class="info-label" style="margin-bottom: 0.5rem;">Payment Details</div>
                            <div class="badge-list">
                                @forelse($paymentDetails as $detail)
                                    <span class="detail-badge">{{ $detail }}</span>
                                @empty
                                    <span class="detail-badge" style="background:#fefce8; border-color:#fcd34d; color:#a16207;">Information Unavailable</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button class="btn btn-outline" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                    <a class="btn btn-primary" href="{{ $redirectUrl ?? url('/') }}">
                        <i class="fas fa-home"></i> Go to Home
                    </a>
                </div>
            </div>
        @endif
    </div>

    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
</body>
</html>



