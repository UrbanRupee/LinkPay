<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Session Expired - DhanKubera</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
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
            box-shadow: 0 20px 60px rgba(217, 119, 6, 0.15);
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
            background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
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
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
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

        .info-box {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-title {
            color: #92400E;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .info-text {
            color: #78350F;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .info-text:last-child {
            margin-bottom: 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #F3F4F6;
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

        .steps {
            margin: 25px 0;
        }

        .step {
            display: flex;
            align-items: start;
            margin-bottom: 15px;
        }

        .step-number {
            width: 32px;
            height: 32px;
            background: #F59E0B;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
            margin-right: 15px;
        }

        .step-text {
            color: #4B5563;
            font-size: 14px;
            line-height: 1.6;
            padding-top: 5px;
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
                <div class="icon">⏱️</div>
            </div>
            <h1>Payment Session Expired</h1>
            <p class="subtitle">Please initiate a new payment</p>
        </div>

        <div class="content">
            <div class="info-box">
                <div class="info-title">What Happened?</div>
                <div class="info-text">
                    Your payment session has expired for security reasons. Payment sessions are only valid for a limited time to protect your transaction.
                </div>
                <div class="info-text">
                    <strong>Don't worry!</strong> No charges were made to your account.
                </div>
            </div>

            @if($txnId)
            <div class="detail-row">
                <span class="label">Previous Transaction ID</span>
                <span class="value">{{ $txnId }}</span>
            </div>
            @endif

            <div class="detail-row">
                <span class="label">Status</span>
                <span class="value" style="color: #F59E0B;">Expired</span>
            </div>

            <div class="steps">
                <h3 style="color: #1F2937; font-size: 16px; margin-bottom: 15px;">To complete your payment:</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-text">Return to your merchant's website or app</div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">Initiate a new payment request</div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">Complete the payment within the valid session time</div>
                </div>
            </div>

            <div class="actions">
                <a href="/" class="btn btn-primary">Go to Homepage</a>
                <a href="mailto:support@dhankubera.com" class="btn btn-secondary">Contact Support</a>
            </div>
        </div>

        <div class="footer">
            Need help? Contact <a href="mailto:support@dhankubera.com" class="support-link">support@dhankubera.com</a>
        </div>
    </div>
</body>
</html>

