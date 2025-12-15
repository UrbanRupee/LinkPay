// NSO Payment Redirection Handler
// This script helps handle NSO payment redirections

(function() {
    'use strict';
    
    // Check if we're on an NSO payment page
    if (window.location.hostname === 'merchant.nsoindia.com') {
        
        // Function to check payment status
        function checkPaymentStatus(transactionId) {
            fetch('https://merchant.xpaisa.in/api/gateway/nso/check-payin-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transaction_id: transactionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status && (data.nso_status === 'success' || data.nso_status === 'completed' || data.local_status == 1)) {
                    // Payment successful, redirect to success page
                    const successUrl = `https://merchant.xpaisa.in/payment-success?txn=${transactionId}&status=success`;
                    window.location.href = successUrl;
                }
            })
            .catch(error => {
                console.log('Status check failed:', error);
            });
        }
        
        // Try to extract transaction ID from URL or page content
        function extractTransactionId() {
            // Try to get from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            let transactionId = urlParams.get('txn') || urlParams.get('transaction_id');
            
            // Try to get from page content (NSO might display it)
            if (!transactionId) {
                const pageText = document.body.innerText;
                const match = pageText.match(/TXN\d+/);
                if (match) {
                    transactionId = match[0];
                }
            }
            
            return transactionId;
        }
        
        // Start checking for payment completion
        const transactionId = extractTransactionId();
        if (transactionId) {
            console.log('NSO Payment detected, transaction ID:', transactionId);
            
            // Check status every 5 seconds
            const statusInterval = setInterval(() => {
                checkPaymentStatus(transactionId);
            }, 5000);
            
            // Stop checking after 10 minutes
            setTimeout(() => {
                clearInterval(statusInterval);
            }, 600000);
        }
        
        // Also listen for page changes (in case NSO uses SPA)
        let lastUrl = location.href;
        new MutationObserver(() => {
            const url = location.href;
            if (url !== lastUrl) {
                lastUrl = url;
                const newTransactionId = extractTransactionId();
                if (newTransactionId && newTransactionId !== transactionId) {
                    checkPaymentStatus(newTransactionId);
                }
            }
        }).observe(document, { subtree: true, childList: true });
    }
})();

