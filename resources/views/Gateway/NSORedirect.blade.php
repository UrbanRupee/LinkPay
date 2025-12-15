<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - Xpaisa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        .logo {
            color: #dc3545;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #dc3545;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            color: #333;
            font-size: 18px;
            margin: 20px 0;
        }
        .amount {
            color: #28a745;
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .transaction-id {
            color: #666;
            font-size: 14px;
            margin: 10px 0;
        }
        .redirect-info {
            color: #666;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">XPAISA</div>
        <div class="spinner"></div>
        <div class="message">Processing your payment...</div>
        <div class="amount">₹{{ $amount }}</div>
        <div class="transaction-id">Transaction ID: {{ $transactionId }}</div>
        <div class="redirect-info">
            You will be redirected automatically after payment completion.
            <br><br>
            <a href="{{ $paymentUrl }}" target="_blank" style="color: #dc3545; text-decoration: none;">
                Click here if you're not redirected automatically
            </a>
        </div>
    </div>

    <script>
        // Open payment page in new tab
        window.open('{{ $paymentUrl }}', '_blank');
        
        // Check payment status every 10 seconds
        let checkCount = 0;
        const maxChecks = 30; // 5 minutes max
        
        function checkPaymentStatus() {
            checkCount++;
            
            if (checkCount > maxChecks) {
                document.querySelector('.message').textContent = 'Payment timeout. Please try again.';
                document.querySelector('.redirect-info').innerHTML = 
                    '<a href="/dashboard" style="color: #dc3545; text-decoration: none;">Return to Dashboard</a>';
                return;
            }
            
            fetch('/api/gateway/nso/check-payin-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    transaction_id: '{{ $transactionId }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status && (data.nso_status === 'success' || data.nso_status === 'completed' || data.local_status == 1)) {
                    // Payment successful, redirect to success page
                    window.location.href = '/payment-success?txn={{ $transactionId }}';
                } else {
                    // Still pending, check again in 10 seconds
                    setTimeout(checkPaymentStatus, 10000);
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
                // Check again in 10 seconds
                setTimeout(checkPaymentStatus, 10000);
            });
        }
        
        // Start checking after 10 seconds
        setTimeout(checkPaymentStatus, 10000);
    </script>
</body>
</html>

