<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xpaisa Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .payment-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 3px solid #dc3545;
        }
        .payment-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #dc3545;
            font-weight: bold;
        }
        .payment-header .amount {
            font-size: 42px;
            font-weight: bold;
            margin: 15px 0;
            color: #ffffff;
        }
        .payment-header .amount-label {
            font-size: 14px;
            opacity: 0.9;
            color: #cccccc;
        }
        .payment-body {
            padding: 30px;
        }
        .qr-section {
            text-align: center;
            margin-bottom: 25px;
        }
        .qr-section h3 {
            color: #1a1a1a;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .qr-code {
            background: white;
            padding: 20px;
            border-radius: 15px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            border: 2px solid #dc3545;
        }
        .qr-code canvas {
            display: block;
        }
        .qr-instructions {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .payment-button {
            width: 100%;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .payment-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.4);
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        }
        .payment-button:active {
            transform: translateY(0);
        }
        .payment-button i {
            font-size: 22px;
        }
        .transaction-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .transaction-info p {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        .transaction-info p strong {
            color: #1a1a1a;
        }
        .security-badge {
            text-align: center;
            padding: 15px;
            color: #28a745;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .security-badge i {
            font-size: 16px;
        }
        .status-checking {
            color: #ffc107;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
            display: none;
        }
        .status-checking.active {
            display: block;
        }
        .success-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .success-overlay.show {
            display: flex;
        }
        .success-message {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            animation: successPop 0.5s ease-out;
        }
        .success-message i {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .success-message h2 {
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        .success-message p {
            color: #666;
            margin-bottom: 20px;
        }
        @keyframes successPop {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        @media (max-width: 480px) {
            .payment-header h1 {
                font-size: 24px;
            }
            .payment-header .amount {
                font-size: 36px;
            }
            .payment-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>XPAISA</h1>
            <p class="amount-label">Amount to Pay</p>
            <div class="amount">₹{{ $amount }}</div>
        </div>
        
        <div class="payment-body">
            <div class="transaction-info">
                <p><span>Transaction ID:</span> <strong>{{ $txnid }}</strong></p>
                <p><span>Status:</span> <strong style="color: #dc3545;">Pending Payment</strong></p>
            </div>

            <div class="qr-section">
                <h3><i class="fas fa-qrcode"></i> Scan QR Code</h3>
                <div class="qr-code" id="qrcode"></div>
                <p class="qr-instructions">
                    Scan this QR code with any UPI app to complete payment
                </p>
            </div>

            <button class="payment-button" onclick="openUPIApp()">
                <i class="fas fa-mobile-alt"></i>
                Pay with UPI App
            </button>

            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Secure Payment Gateway</span>
            </div>

            <div class="status-checking" id="statusChecking">
                <i class="fas fa-spinner fa-spin"></i> Checking payment status...
            </div>
        </div>
    </div>

    <!-- Success Overlay -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h2>Payment Successful!</h2>
            <p>Your payment of ₹{{ $amount }} has been received.</p>
            <p style="color: #28a745; font-weight: bold;">Redirecting...</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Dynamic UPI Intent URL from database (already includes amount)
        const upiUrl = "{!! str_replace('\/', '/', $upi_string) !!}";
        const amount = "{{ $amount }}";
        const txnId = "{{ $txnid }}";
        const maxCheckTime = 5 * 60 * 1000; // 5 minutes
        const checkInterval = 3000; // Check every 3 seconds
        let startTime = Date.now();
        let statusCheckTimer = null;
        
        // Generate QR Code with dynamic UPI string
        new QRCode(document.getElementById("qrcode"), {
            text: upiUrl,
            width: 220,
            height: 220,
            colorDark: "#dc3545",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Open UPI App with dynamic intent
        function openUPIApp() {
            window.location.href = upiUrl;
            // Start checking status after user clicks pay button
            startStatusChecking();
        }

        // Check transaction status via AJAX
        function checkPaymentStatus() {
            const elapsed = Date.now() - startTime;
            
            // Stop checking after 5 minutes
            if (elapsed > maxCheckTime) {
                clearInterval(statusCheckTimer);
                document.getElementById('statusChecking').classList.remove('active');
                return;
            }

            fetch('/api/check-payment-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ transaction_id: txnId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.status === 1) {
                    // Payment successful - show success message and redirect
                    clearInterval(statusCheckTimer);
                    showSuccessAndRedirect();
                } else if (data.status === 'failed' || data.status === 2) {
                    // Payment failed - stop checking
                    clearInterval(statusCheckTimer);
                    document.getElementById('statusChecking').classList.remove('active');
                    alert('Payment failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error checking status:', error);
            });
        }

        // Start status checking
        function startStatusChecking() {
            document.getElementById('statusChecking').classList.add('active');
            statusCheckTimer = setInterval(checkPaymentStatus, checkInterval);
        }

        // Show success message and redirect
        function showSuccessAndRedirect() {
            document.getElementById('successOverlay').classList.add('show');
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = 'https://merchant.xpaisa.in/payment-success?txn=' + txnId;
            }, 2000);
        }

        // Auto-redirect on mobile devices
        if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            // On mobile, show a countdown before auto-opening UPI app
            setTimeout(() => {
                const confirmPay = confirm('Open UPI app to complete payment of ₹' + amount + '?');
                if (confirmPay) {
                    openUPIApp();
                }
            }, 1000);
        }

        // Start checking status immediately on page load
        setTimeout(() => {
            startStatusChecking();
        }, 5000); // Start checking after 5 seconds
    </script>
</body>
</html>
