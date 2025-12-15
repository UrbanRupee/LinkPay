<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - DhanKubera</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #FFEDD5 0%, #FED7AA 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(241, 90, 34, 0.15);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .icon {
            font-size: 48px;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 16px;
            opacity: 0.95;
        }

        .content {
            padding: 40px 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #6B7280;
            font-size: 14px;
            font-weight: 500;
        }

        .value {
            color: #1F2937;
            font-size: 16px;
            font-weight: 600;
            text-align: right;
        }

        .amount {
            font-size: 32px;
            color: #DC2626;
            font-weight: 700;
        }

        .reason-box {
            background: #FEE2E2;
            border-left: 4px solid #DC2626;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .reason-label {
            color: #991B1B;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .reason-text {
            color: #DC2626;
            font-size: 14px;
            line-height: 1.5;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #F15A22;
            color: white;
        }

        .btn-primary:hover {
            background: #D14A15;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(241, 90, 34, 0.3);
        }

        .btn-secondary {
            background: #F3F4F6;
            color: #1F2937;
        }

        .btn-secondary:hover {
            background: #E5E7EB;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #6B7280;
            font-size: 13px;
            background: #F9FAFB;
        }

        .support-link {
            color: #F15A22;
            text-decoration: none;
            font-weight: 600;
        }

        .support-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                border-radius: 0;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .amount {
                font-size: 28px;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon-wrapper">
                <div class="icon">❌</div>
            </div>
            <h1>Payment Failed</h1>
            <p class="subtitle">Your transaction could not be completed</p>
        </div>

        <div class="content">
            @if($txnId)
            <div class="detail-row">
                <span class="label">Transaction ID</span>
                <span class="value">{{ $txnId }}</span>
            </div>
            @endif

            @if($amount)
            <div class="detail-row">
                <span class="label">Amount</span>
                <span class="value amount">₹{{ number_format($amount, 2) }}</span>
            </div>
            @endif

            <div class="detail-row">
                <span class="label">Status</span>
                <span class="value" style="color: #DC2626;">Failed</span>
            </div>

            <div class="detail-row">
                <span class="label">Date & Time</span>
                <span class="value">{{ date('d M Y, h:i A') }}</span>
            </div>

            @if($reason)
            <div class="reason-box">
                <div class="reason-label">Failure Reason</div>
                <div class="reason-text">{{ $reason }}</div>
            </div>
            @endif

            <div class="actions">
                @if($txnId)
                    <a href="{{ url('/retry-payment?txn=' . $txnId) }}" class="btn btn-primary">Try Again</a>
                @else
                    <a href="/" class="btn btn-primary">Try New Payment</a>
                @endif
                <a href="{{ $redirectUrl ?? url('/') }}" class="btn btn-secondary">Go Home</a>
            </div>
        </div>

        <div class="footer">
            Need help? Contact <a href="mailto:support@dhankubera.com" class="support-link">support@dhankubera.com</a>
        </div>
    </div>
</body>
</html>




