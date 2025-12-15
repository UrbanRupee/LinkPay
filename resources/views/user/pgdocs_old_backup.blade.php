@extends('user.layout.NewUser')

@section('css')
    <style>
        /* RED & BLACK THEME - API Documentation */
        .api-docs-container .box {
            margin-bottom: 1.5rem;
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
        }
        .api-docs-container h1,
        .api-docs-container h2,
        .api-docs-container h3,
        .api-docs-container h4,
        .api-docs-container h5,
        .api-docs-container h6 {
            color: var(--primary-text-color);
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        .api-docs-container h1 { font-size: 2.25rem; color: var(--primary-red); }
        .api-docs-container h2 { font-size: 1.75rem; border-bottom: 2px solid var(--primary-red); padding-bottom: 0.5rem; }
        .api-docs-container h3 { font-size: 1.5rem; color: var(--light-red); }
        .api-docs-container h4 { font-size: 1.25rem; }

        /* Styling for paragraphs and lists */
        .api-docs-container p {
            line-height: 1.6;
            margin-bottom: 1rem;
            color: var(--primary-text-color);
        }
        .api-docs-container ul {
            list-style-type: disc;
            margin-left: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-text-color);
        }
        .api-docs-container ul li {
            margin-bottom: 0.5rem;
        }

        /* Styling for code blocks */
        .api-docs-container pre {
            background-color: var(--black);
            border: 1px solid var(--primary-red);
            border-radius: var(--border-radius-md);
            padding: 1rem;
            overflow-x: auto;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1.5rem;
            color: var(--light-red);
        }
        .api-docs-container code {
            background-color: var(--medium-gray);
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            font-size: 0.9em;
            color: var(--primary-red);
            border: 1px solid var(--primary-red);
        }
        .api-docs-container pre code {
            background-color: transparent;
            padding: 0;
            color: #FCA5A5;
            border: none;
        }

        /* Styling for tables - RED & BLACK THEME */
        .api-docs-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            background-color: var(--dark-gray);
            border: 1px solid var(--primary-red);
            border-radius: var(--border-radius-md);
            overflow: hidden;
        }
        .api-docs-container table th,
        .api-docs-container table td {
            border: 1px solid var(--medium-gray);
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
            color: #FFFFFF;
        }
        .api-docs-container table thead th {
            background-color: var(--black);
            font-weight: 600;
            color: var(--primary-red);
            white-space: nowrap;
        }
        .api-docs-container table tbody tr:nth-child(even) {
            background-color: rgba(220, 38, 38, 0.05);
        }
        .api-docs-container table tbody tr:hover {
            background-color: rgba(220, 38, 38, 0.1);
        }
        /* Specific styling for table header cells from markdown */
        .api-docs-container table th:first-child { width: 15%; } /* Parameter */
        .api-docs-container table th:nth-child(2) { width: 10%; } /* Type */
        .api-docs-container table th:nth-child(3) { width: 10%; } /* Required */
        .api-docs-container table th:last-child { width: 65%; } /* Description */

        /* Important notes - RED & BLACK THEME */
        .api-docs-container strong {
            color: #FFFFFF;
            font-weight: 600;
        }
        .api-docs-container .important-note {
            background-color: rgba(220, 38, 38, 0.1);
            border-left: 4px solid var(--primary-red);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius-md);
            color: #FFFFFF;
            font-size: 0.95rem;
        }
        
        .api-docs-container .api-docs-content {
            background: var(--black);
            padding: 2rem;
            border-radius: 12px;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid api-docs-container" style="background: var(--black); padding: 2rem; min-height: 100vh;">
    <div class="box p-4">
        <h1>xpaisa API Documentation</h1>
        <p>Welcome to the xpaisa API documentation. This guide provides details on integrating with our payment gateway for various operations, including initiating payments (Payin), processing payouts, and checking wallet balances.</p>

        <h2>General Information</h2>
        <ul>
            <li>All API requests should be sent as <strong>JSON</strong> in the request body.</li>
            <li>All responses will be in <strong>JSON</strong> format.</li>
            <li><strong>Base URL:</strong> <code>https://merchant.xpaisa.in/api/</code></li>
            <li><strong>Placeholders:</strong>
                <ul>
                    <li><code>TOKEN</code>: Your unique API authentication token.</li>
                    <li><code>USERID</code>: Your unique merchant User ID.</li>
                    <li><code>YOUR_CALLBACK_URL</code>: The URL on your system where we will send callback notifications.</li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="box p-4 mt-3">
        <h2>1. Payin API</h2>

        <h3>1.1 Initiate Payin</h3>
        <p>Initiate a payment request from a customer.</p>
        <h4>Endpoint:</h4>
        <pre><code>POST https://merchant.xpaisa.in/api/pg/urbanpay/initiate</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>token</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your API authentication token.</td>
                </tr>
                <tr>
                    <td><code>userid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your unique merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Amount to be paid (e.g., "31" for 31.00).</td>
                </tr>
                <tr>
                    <td><code>mobile</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's mobile number.</td>
                </tr>
                <tr>
                    <td><code>name</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's name.</td>
                </tr>
                <tr>
                    <td><code>orderid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Unique order ID from your system.It must be exactly 20 characters long, Only use alphabets (A–Z, a–z) and numbers (0–9)</td>
                </tr>
                <tr>
                    <td><code>callback_url</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>URL on your system to receive payment status updates (POST).</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "token": "YOUR_API_TOKEN",
    "userid": "YOUR_USER_ID",
    "amount": "100",
    "mobile": "9112345322",
    "name": "Adarsh",
    "orderid": "xpaisaPayin00001234",
    "callback_url": "https://YOURDOMAIN.COM/payin/callback"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": true,
    "message": "success",
    "url": "upi://..."
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": false,
    "message": "Along message detailing the error"
}</code></pre>

        <h3>1.2 Easebuzz PayIn Integration</h3>
        <p>Easebuzz provides seamless payment integration with support for UPI, Credit Cards, Net Banking, and Mobile Wallets.</p>
        
        <h4>Gateway ID: 28</h4>
        <p>To use Easebuzz, set your <code>payingateway</code> to <code>28</code> in your user profile.</p>
        
        <h4>Supported Payment Modes:</h4>
        <ul>
            <li><strong>UPI:</strong> All UPI apps (PhonePe, Google Pay, Paytm, BHIM, etc.)</li>
            <li><strong>Credit Cards:</strong> Visa, Mastercard, RuPay</li>
            <li><strong>Debit Cards:</strong> Visa, Mastercard, RuPay</li>
            <li><strong>Net Banking:</strong> 50+ banks</li>
            <li><strong>Mobile Wallets:</strong> Paytm, PhonePe, Freecharge, Mobikwik</li>
        </ul>
        
        <h4>Easebuzz API Endpoints:</h4>
        <table>
            <thead>
                <tr>
                    <th>Environment</th>
                    <th>Base URL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Test/UAT</td>
                    <td><code>https://testpay.easebuzz.in</code></td>
                </tr>
                <tr>
                    <td>Production</td>
                    <td><code>https://pay.easebuzz.in</code></td>
                </tr>
            </tbody>
        </table>
        
        <h4>Integration Flow:</h4>
        <ol>
            <li><strong>Initiate Payment:</strong> Call our PayIn API with gateway ID 28</li>
            <li><strong>Redirect Customer:</strong> Redirect customer to the returned payment URL</li>
            <li><strong>Payment Processing:</strong> Customer completes payment on Easebuzz</li>
            <li><strong>Callback:</strong> Easebuzz sends status to your callback URL</li>
            <li><strong>Confirmation:</strong> We update your wallet and send confirmation</li>
        </ol>
        
        <h4>Callback Parameters:</h4>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>status</code></td>
                    <td>String</td>
                    <td>Payment status: <code>success</code> or <code>failed</code></td>
                </tr>
                <tr>
                    <td><code>order_id</code></td>
                    <td>String</td>
                    <td>Your order ID</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>String</td>
                    <td>Transaction amount</td>
                </tr>
                <tr>
                    <td><code>payment_id</code></td>
                    <td>String</td>
                    <td>Easebuzz payment ID</td>
                </tr>
                <tr>
                    <td><code>hash</code></td>
                    <td>String</td>
                    <td>Hash for verification</td>
                </tr>
            </tbody>
        </table>
        
        <h4>Example Callback Response:</h4>
        <pre><code>{
    "status": "success",
    "order_id": "xpaisaPayin00001234",
    "amount": "100.00",
    "payment_id": "EBZ123456789",
    "hash": "generated_hash_string"
}</code></pre>
        
        <h4>Hash Verification:</h4>
        <p>Always verify the hash to ensure the callback is from Easebuzz:</p>
        <pre><code>// Create hash string excluding 'hash' parameter
$hashString = 'status=success~order_id=xpaisaPayin00001234~amount=100.00~payment_id=EBZ123456789~YOUR_SALT';
$generatedHash = hash('sha512', $hashString);

// Compare with received hash
if (hash_equals($generatedHash, $receivedHash)) {
    // Valid callback
}</code></pre>

        <h3>1.3 Payin Callback</h3>
        <p>Xpaisa will send asynchronous payment status updates to the <code>callback_url</code> you provided during initiation.</p>
        <h4>Endpoint:</h4>
        <pre><code>POST YOUR_CALLBACK_URL</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>status</code></td>
                    <td>String</td>
                    <td>Payment status: <code>success</code> or <code>failed</code>.</td>
                </tr>
                <tr>
                    <td><code>client_txn_id</code></td>
                    <td>String</td>
                    <td>Transaction ID generated by your system (your <code>orderid</code>).</td>
                </tr>
                <tr>
                    <td><code>utr</code></td>
                    <td>String</td>
                    <td>Unique Transaction Reference (UTR) from the payment network.</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>String</td>
                    <td>The transaction amount.</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Callback Payload:</h4>
        <pre><code>{
    "status": "success",
    "client_txn_id": "CKC1923605395260219392",
    "utr": "409196790567",
    "amount": "100.00"
}</code></pre>
        <p class="important-note"><strong>Note:</strong> Ensure your callback URL is publicly accessible and configured to handle POST requests from xpaisa. The <code>client_txn_id</code> in the callback will correspond to the <code>orderid</code> you sent during initiation.</p>

        <h3>1.3 Check Payin Status</h3>
        <p>Retrieve the current status of a Payin transaction.</p>
        <h4>Endpoint:</h4>
        <pre><code>POST https://merchant.xpaisa.in/api/pg/urbanpay/checkstatus</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>userid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your unique merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>orderid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>The unique order ID from your system (<code>client_txn_id</code>).</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "userid": "MP1d57ddd71",
    "orderid": "122d7dddd4442"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": "success",
    "message": "",
    "utr": "491850579611",
    "client_txn_id": "PINR1001541934930597327802368"
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": "failed",
    "message": "Invalid Order Id!!"
}</code></pre>
    </div>

    <div class="box p-4 mt-3">
        <h2>2. Payout API</h2>

        <h3>2.1 Initiate Payout</h3>
        <p>Initiate a payout request to send funds to a beneficiary.</p>
        <h4>Endpoint:</h4>
        <pre><code>POST https://merchant.xpaisa.in/api/payout/initiate</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>token</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your API authentication token.</td>
                </tr>
                <tr>
                    <td><code>userid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your unique merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Amount to be paid out.</td>
                </tr>
                <tr>
                    <td><code>mobile</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Beneficiary's mobile number.</td>
                </tr>
                <tr>
                    <td><code>name</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Beneficiary's name.</td>
                </tr>
                <tr>
                    <td><code>number</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Beneficiary's bank account number or UPI ID.</td>
                </tr>
                <tr>
                    <td><code>ifsc</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Beneficiary's IFSC code (for bank transfers).</td>
                </tr>
                <tr>
                    <td><code>orderid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Unique order ID from your system.</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "token": "YOUR_API_TOKEN",
    "userid": "YOUR_USER_ID",
    "amount": "10",
    "mobile": "7060471592",
    "name": "ADARSH PUSHPENDRA PANDEY",
    "number": "923020068454751",
    "ifsc": "UTIB0001691",
    "orderid": "adadrddshtxestddtanwar00s3"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": true,
    "message": "Success",
    "id": "TRANSACTION_ID_BY_xpaisa",
    "dataByBank": true
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": false,
    "message": "Error message detailing the failure",
    "id": ""
}</code></pre>
        <p class="important-note"><strong>Important:</strong> Your IP addresses must be whitelisted for payout initiation. Please contact support to get your IPs whitelisted.</p>

        <h3>2.2 Payout Callback</h3>
        <p>xpaisa will send asynchronous payout status updates to your configured callback URL.</p>
        <h4>Endpoint:</h4>
        <pre><code>POST YOUR_CALLBACK_URL_FOR_PAYOUTS</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>status</code></td>
                    <td>String</td>
                    <td>Payout status: <code>success</code> or <code>refund</code> (failed/returned).</td>
                </tr>
                <tr>
                    <td><code>remark</code></td>
                    <td>String</td>
                    <td>Optional: A message or reason for the status.</td>
                </tr>
                <tr>
                    <td><code>transaction_id</code></td>
                    <td>String</td>
                    <td>Transaction ID generated by xpaisa for this payout.</td>
                </tr>
                <tr>
                    <td><code>utr</code></td>
                    <td>String</td>
                    <td>Unique Transaction Reference (UTR) from the payment network.</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Callback Payload:</h4>
        <pre><code>{
    "status": "success",
    "remark": "Funds successfully disbursed.",
    "transaction_id": "MP2024120749287112",
    "utr": "688148704280"
}</code></pre>
        <p class="important-note"><strong>Note:</strong> Ensure your callback URL for payouts is configured in your xpaisa merchant panel and is publicly accessible.</p>

        <h3>2.3 Check Payout Status</h3>
        <p>Retrieve the current status of a Payout transaction.</p>
        <h4>Endpoint:</h4>
        <pre><code>POST https://merchant.xpaisa.in/api/payout/checkstatus</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>userid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your unique merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>orderid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>The unique order ID from your system (<code>client_txn_id</code>).</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "userid": "RXP10100",
    "orderid": "SINR1002051920886767314145280"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": "success",
    "message": "",
    "utr": "CMP00000000009395684",
    "client_txn_id": "SINR1002051920886767314145280"
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": "failed",
    "message": "Invalid Order Id!!"
}</code></pre>
    </div>

    <div class="box p-4 mt-3">
        <h2>3. Easebuzz Payment Gateway API</h2>

        <p>Easebuzz is a comprehensive payment gateway that supports UPI, Net Banking, Cards, Wallets, and more. This documentation covers integration for Easebuzz Seamless (Merchant Hosted) and Standard payment flows.</p>
        
        <div class="alert alert-info">
            <strong>🔐 Integration Type:</strong> Seamless Integration (Merchant Hosted)
            <br><strong>🌐 Environments:</strong> UAT (Testing) & Production
            <br><strong>🔑 Required:</strong> Merchant Key & Salt from Easebuzz
        </div>

        <h3>3.1 Overview</h3>
        <p>In Easebuzz Seamless Integration, customers stay on your website without being redirected. The payment flow happens in an embedded manner with AES-256 encryption for security.</p>
        
        <h4>Key Features:</h4>
        <ul>
            <li>✅ <strong>No Redirection:</strong> Customers stay on your website</li>
            <li>✅ <strong>Multiple Payment Modes:</strong> Cards, UPI, Net Banking, Wallets, EMI</li>
            <li>✅ <strong>AES-256 Encryption:</strong> Secure card data handling</li>
            <li>✅ <strong>UPI Deep Linking:</strong> Direct UPI app opening or QR code</li>
            <li>✅ <strong>Native OTP:</strong> Card transactions with OTP on your page</li>
            <li>✅ <strong>PCI/DSS Compliant:</strong> For merchants who are certified</li>
        </ul>

        <h3>3.2 Easebuzz API Endpoints</h3>
        <table>
            <thead>
                <tr>
                    <th>Environment</th>
                    <th>Initiate Payment</th>
                    <th>Seamless Payment</th>
                    <th>Transaction Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>UAT (Testing)</strong></td>
                    <td><code>testpay.easebuzz.in/payment/initiateLink</code></td>
                    <td><code>testpay.easebuzz.in/initiate_seamless_payment/</code></td>
                    <td><code>testpay.easebuzz.in/transaction/v2.1/retrieve</code></td>
                </tr>
                <tr>
                    <td><strong>Production</strong></td>
                    <td><code>pay.easebuzz.in/payment/initiateLink</code></td>
                    <td><code>pay.easebuzz.in/initiate_seamless_payment/</code></td>
                    <td><code>pay.easebuzz.in/transaction/v2.1/retrieve</code></td>
                </tr>
            </tbody>
        </table>

        <h3>3.3 Integration Flow</h3>
        <ol>
            <li><strong>Generate Access Key:</strong> Call Initiate Payment API with <code>request_flow: "SEAMLESS"</code></li>
            <li><strong>Initiate Payment:</strong> Use the access_key to initiate seamless payment</li>
            <li><strong>Handle Response:</strong> Capture status from SURL/FURL (Success/Failure URLs)</li>
            <li><strong>Verify Transaction:</strong> Use Transaction API to confirm payment status</li>
            <li><strong>Webhook Integration:</strong> Configure webhooks for real-time updates</li>
        </ol>

        <h3>3.4 Step 1: Generate Access Key</h3>
        <h4>Endpoint:</h4>
        <pre><code>POST https://pay.easebuzz.in/payment/initiateLink</code></pre>

        <h4>Request Parameters:</h4>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>key</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your Easebuzz Merchant Key</td>
                </tr>
                <tr>
                    <td><code>txnid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Unique transaction ID (alphanumeric, max 25 chars)</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Transaction amount (e.g., "100.00")</td>
                </tr>
                <tr>
                    <td><code>productinfo</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Product description</td>
                </tr>
                <tr>
                    <td><code>firstname</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's first name</td>
                </tr>
                <tr>
                    <td><code>email</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's email address</td>
                </tr>
                <tr>
                    <td><code>phone</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's mobile number (10 digits)</td>
                </tr>
                <tr>
                    <td><code>surl</code></td>
                    <td>String (URL)</td>
                    <td>Yes</td>
                    <td>Success callback URL</td>
                </tr>
                <tr>
                    <td><code>furl</code></td>
                    <td>String (URL)</td>
                    <td>Yes</td>
                    <td>Failure callback URL</td>
                </tr>
                <tr>
                    <td><code>hash</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>SHA-512 hash (see hash generation below)</td>
                </tr>
                <tr>
                    <td><code>request_flow</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td><strong>"SEAMLESS"</strong> for seamless integration</td>
                </tr>
                <tr>
                    <td><code>udf1-udf10</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>User-defined fields for custom data</td>
                </tr>
                <tr>
                    <td><code>address1</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>Customer address line 1</td>
                </tr>
                <tr>
                    <td><code>city</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>City</td>
                </tr>
                <tr>
                    <td><code>state</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>State</td>
                </tr>
                <tr>
                    <td><code>country</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>Country</td>
                </tr>
                <tr>
                    <td><code>zipcode</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>ZIP code</td>
                </tr>
            </tbody>
        </table>

        <h4>Hash Generation Formula:</h4>
        <pre><code>hash = SHA512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10|salt)</code></pre>

        <h4>PHP Example - Generate Hash:</h4>
        <pre><code>$key = "YOUR_MERCHANT_KEY";
$salt = "YOUR_SALT";

$hashString = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' 
            . $firstname . '|' . $email . '|' . ($udf1 ?? '') . '|' . ($udf2 ?? '') 
            . '|' . ($udf3 ?? '') . '|' . ($udf4 ?? '') . '|' . ($udf5 ?? '') 
            . '|' . ($udf6 ?? '') . '|' . ($udf7 ?? '') . '|' . ($udf8 ?? '') 
            . '|' . ($udf9 ?? '') . '|' . ($udf10 ?? '') . '|' . $salt;

$hash = hash('sha512', $hashString);</code></pre>

        <h4>Example Request:</h4>
        <pre><code>{
    "key": "YOUR_MERCHANT_KEY",
    "txnid": "TXN123456789",
    "amount": "100.00",
    "productinfo": "Payment for Order",
    "firstname": "John Doe",
    "email": "john@example.com",
    "phone": "9876543210",
    "surl": "https://yourdomain.com/easebuzz/success",
    "furl": "https://yourdomain.com/easebuzz/failure",
    "hash": "generated_hash_here",
    "request_flow": "SEAMLESS",
    "udf1": "custom_data",
    "address1": "123 Street",
    "city": "Mumbai",
    "state": "Maharashtra",
    "country": "India",
    "zipcode": "400001"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": 1,
    "data": "2bb40c0e987d09e0d4307daf4169fb44a88cc145f56c7980c5da9be0b78a5c55",
    "message": "Access key generated successfully"
}</code></pre>

        <p class="important-note"><strong>Important:</strong> Store the <code>data</code> value (access_key) - you'll need it for the next step!</p>

        <h3>3.5 Step 2: Initiate Seamless Payment</h3>
        <p>After receiving the access_key, initiate the actual payment with customer's payment details.</p>
        
        <h4>Endpoint:</h4>
        <pre><code>POST https://pay.easebuzz.in/initiate_seamless_payment/
Content-Type: application/x-www-form-urlencoded</code></pre>

        <h4>Request Parameters:</h4>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>access_key</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Access key from Step 1</td>
                </tr>
                <tr>
                    <td><code>payment_mode</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>NB, DC, CC, MW, UPI, OM, PL, EMI</td>
                </tr>
                <tr>
                    <td><code>bank_code</code></td>
                    <td>String</td>
                    <td>Conditional</td>
                    <td>Required for Net Banking & Wallets</td>
                </tr>
                <tr>
                    <td><code>card_number</code></td>
                    <td>String (Encrypted)</td>
                    <td>Conditional</td>
                    <td>Required for DC, CC, EMI (encrypted)</td>
                </tr>
                <tr>
                    <td><code>card_holder_name</code></td>
                    <td>String (Encrypted)</td>
                    <td>Conditional</td>
                    <td>Required for DC, CC, EMI (encrypted)</td>
                </tr>
                <tr>
                    <td><code>card_cvv</code></td>
                    <td>String (Encrypted)</td>
                    <td>Conditional</td>
                    <td>Required for DC, CC, EMI (encrypted)</td>
                </tr>
                <tr>
                    <td><code>card_expiry_date</code></td>
                    <td>String (Encrypted)</td>
                    <td>Conditional</td>
                    <td>MM/YYYY format (encrypted)</td>
                </tr>
                <tr>
                    <td><code>upi_va</code></td>
                    <td>String</td>
                    <td>Conditional</td>
                    <td>Required for UPI (VPA/UPI ID)</td>
                </tr>
                <tr>
                    <td><code>upi_qr</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>"true" for UPI QR/Deep Linking</td>
                </tr>
                <tr>
                    <td><code>request_mode</code></td>
                    <td>String</td>
                    <td>No</td>
                    <td>"SUVA" for JSON response, "S2S" for Native OTP</td>
                </tr>
            </tbody>
        </table>

        <h3>3.6 Card Data Encryption (AES-256)</h3>
        <p class="important-note"><strong>⚠️ Security Requirement:</strong> Card details MUST be encrypted using AES-256-CBC before sending!</p>
        
        <h4>Encryption Guidelines:</h4>
        <ul>
            <li><strong>Algorithm:</strong> AES-256 in CBC mode</li>
            <li><strong>KEY:</strong> First 32 bytes of SHA-256 hash of MERCHANT_KEY</li>
            <li><strong>IV:</strong> First 16 bytes of SHA-256 hash of SALT</li>
            <li><strong>Padding:</strong> PKCS7 padding (multiples of 16)</li>
        </ul>

        <h4>PHP Encryption Example:</h4>
        <pre><code>function encryptCardData($data, $merchantKey, $salt) {
    // Generate KEY: First 32 bytes of SHA-256 hash of MERCHANT_KEY
    $key = substr(hash('sha256', $merchantKey, true), 0, 32);
    
    // Generate IV: First 16 bytes of SHA-256 hash of SALT
    $iv = substr(hash('sha256', $salt, true), 0, 16);
    
    // PKCS7 Padding
    $blockSize = 16;
    $pad = $blockSize - (strlen($data) % $blockSize);
    $data .= str_repeat(chr($pad), $pad);
    
    // Encrypt using AES-256-CBC
    $encrypted = openssl_encrypt(
        $data,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
        $iv
    );
    
    return base64_encode($encrypted);
}

// Example usage:
$merchantKey = "YOUR_MERCHANT_KEY";
$salt = "YOUR_SALT";

$cardNumber = encryptCardData("4111111111111111", $merchantKey, $salt);
$cardHolderName = encryptCardData("John Doe", $merchantKey, $salt);
$cardCVV = encryptCardData("123", $merchantKey, $salt);
$cardExpiry = encryptCardData("12/2025", $merchantKey, $salt);</code></pre>

        <h3>3.7 Payment Modes & Examples</h3>
        
        <h4>3.7.1 UPI Payment with Deep Link/QR</h4>
        <pre><code>// Request
{
    "access_key": "your_access_key_here",
    "payment_mode": "UPI",
    "upi_qr": "true",
    "request_mode": "SUVA"
}

// Response
{
    "status": true,
    "msg_desc": "Scan the QR Code on any UPI Application",
    "qr_link": "upi://pay?pa=merchant@upi&pn=EASEBUZZ&tr=TXN123&am=100.0&cu=INR",
    "msg_title": ""
}</code></pre>

        <h4>3.7.2 UPI Payment with VPA</h4>
        <pre><code>{
    "access_key": "your_access_key_here",
    "payment_mode": "UPI",
    "upi_va": "customer@paytm"
}</code></pre>

        <h4>3.7.3 Credit Card Payment (Encrypted)</h4>
        <pre><code>{
    "access_key": "your_access_key_here",
    "payment_mode": "CC",
    "card_number": "ENCRYPTED_CARD_NUMBER",
    "card_holder_name": "ENCRYPTED_HOLDER_NAME",
    "card_cvv": "ENCRYPTED_CVV",
    "card_expiry_date": "ENCRYPTED_EXPIRY"
}</code></pre>

        <h4>3.7.4 Net Banking</h4>
        <pre><code>{
    "access_key": "your_access_key_here",
    "payment_mode": "NB",
    "bank_code": "HDFC"
}</code></pre>

        <h4>3.7.5 Mobile Wallet</h4>
        <pre><code>{
    "access_key": "your_access_key_here",
    "payment_mode": "MW",
    "bank_code": "paytm"
}</code></pre>

        <h3>3.8 Callback Handling (SURL/FURL)</h3>
        <p>Easebuzz will send transaction updates to your Success URL (SURL) or Failure URL (FURL).</p>
        
        <h4>Callback Parameters:</h4>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>txnid</code></td>
                    <td>Your transaction ID</td>
                </tr>
                <tr>
                    <td><code>status</code></td>
                    <td>success, failure, pending, userCancelled</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>Transaction amount</td>
                </tr>
                <tr>
                    <td><code>email</code></td>
                    <td>Customer email</td>
                </tr>
                <tr>
                    <td><code>firstname</code></td>
                    <td>Customer name</td>
                </tr>
                <tr>
                    <td><code>productinfo</code></td>
                    <td>Product description</td>
                </tr>
                <tr>
                    <td><code>easepayid</code></td>
                    <td>Easebuzz transaction ID</td>
                </tr>
                <tr>
                    <td><code>bank_ref_num</code></td>
                    <td>Bank reference/UTR number</td>
                </tr>
                <tr>
                    <td><code>error_Message</code></td>
                    <td>Error message (if failed)</td>
                </tr>
                <tr>
                    <td><code>hash</code></td>
                    <td>Response hash for verification</td>
                </tr>
            </tbody>
        </table>

        <h4>Verify Response Hash:</h4>
        <pre><code>// Reverse hash sequence
$hashString = $salt . '|' . $status . '|' . $udf10 . '|' . $udf9 . '|' . $udf8 
            . '|' . $udf7 . '|' . $udf6 . '|' . $udf5 . '|' . $udf4 . '|' . $udf3 
            . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname 
            . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;

$generatedHash = hash('sha512', $hashString);

if ($generatedHash === $receivedHash) {
    // Hash verified - process callback
} else {
    // Invalid hash - reject
}</code></pre>

        <h3>3.9 Transaction Status API</h3>
        <h4>Endpoint:</h4>
        <pre><code>POST https://pay.easebuzz.in/transaction/v2.1/retrieve</code></pre>

        <h4>Request Parameters:</h4>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>key</code></td>
                    <td>Yes</td>
                    <td>Merchant Key</td>
                </tr>
                <tr>
                    <td><code>txnid</code></td>
                    <td>Yes</td>
                    <td>Transaction ID</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>Yes</td>
                    <td>Transaction amount</td>
                </tr>
                <tr>
                    <td><code>email</code></td>
                    <td>Yes</td>
                    <td>Customer email</td>
                </tr>
                <tr>
                    <td><code>phone</code></td>
                    <td>Yes</td>
                    <td>Customer phone</td>
                </tr>
                <tr>
                    <td><code>hash</code></td>
                    <td>Yes</td>
                    <td>SHA-512(key|txnid|amount|email|phone|salt)</td>
                </tr>
            </tbody>
        </table>

        <h3>3.10 Transaction Statuses</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>PreInitiated</code></td>
                    <td>Access key generated</td>
                    <td>Proceed to payment initiation</td>
                </tr>
                <tr>
                    <td><code>Initiated</code></td>
                    <td>Customer on payment/checkout page</td>
                    <td>Wait for customer action</td>
                </tr>
                <tr>
                    <td><code>Pending</code></td>
                    <td>Customer on bank page</td>
                    <td>Wait for bank response</td>
                </tr>
                <tr>
                    <td><code>Success</code></td>
                    <td>Transaction successful</td>
                    <td>Credit to wallet</td>
                </tr>
                <tr>
                    <td><code>Failure</code></td>
                    <td>Transaction failed</td>
                    <td>Show error to customer</td>
                </tr>
                <tr>
                    <td><code>Usercancelled</code></td>
                    <td>Customer cancelled</td>
                    <td>Allow retry</td>
                </tr>
                <tr>
                    <td><code>Dropped</code></td>
                    <td>Customer left bank page</td>
                    <td>Mark as abandoned</td>
                </tr>
                <tr>
                    <td><code>Bounced</code></td>
                    <td>Access key expired</td>
                    <td>Generate new access key</td>
                </tr>
            </tbody>
        </table>

        <h3>3.11 Supported Payment Modes</h3>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Payment Mode</th>
                    <th>Additional Fields Required</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>NB</code></td>
                    <td>Net Banking</td>
                    <td>bank_code</td>
                </tr>
                <tr>
                    <td><code>DC</code></td>
                    <td>Debit Card</td>
                    <td>card_number, card_holder_name, card_cvv, card_expiry_date (all encrypted)</td>
                </tr>
                <tr>
                    <td><code>CC</code></td>
                    <td>Credit Card</td>
                    <td>card_number, card_holder_name, card_cvv, card_expiry_date (all encrypted)</td>
                </tr>
                <tr>
                    <td><code>MW</code></td>
                    <td>Mobile Wallet</td>
                    <td>bank_code (wallet code)</td>
                </tr>
                <tr>
                    <td><code>UPI</code></td>
                    <td>UPI</td>
                    <td>upi_va or upi_qr</td>
                </tr>
                <tr>
                    <td><code>OM</code></td>
                    <td>Ola Money</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td><code>PL</code></td>
                    <td>Pay Later</td>
                    <td>pay_later_app (e.g., "Simpl")</td>
                </tr>
                <tr>
                    <td><code>EMI</code></td>
                    <td>EMI</td>
                    <td>emi_object, card details (encrypted)</td>
                </tr>
            </tbody>
        </table>

        <h3>3.12 Testing & Integration</h3>
        
        <h4>3.12.1 Integration Checklist</h4>
        <ul>
            <li><strong>✅ Get Credentials:</strong> Obtain Merchant Key & Salt from Easebuzz</li>
            <li><strong>✅ Test Environment:</strong> Start with UAT endpoints</li>
            <li><strong>✅ Encryption:</strong> Implement AES-256 encryption for cards</li>
            <li><strong>✅ Hash Generation:</strong> Correctly generate SHA-512 hashes</li>
            <li><strong>✅ Callbacks:</strong> Set up SURL and FURL endpoints</li>
            <li><strong>✅ Webhooks:</strong> Configure webhooks for UPI/Native OTP</li>
            <li><strong>✅ Status Verification:</strong> Always verify using Transaction API</li>
            <li><strong>✅ Error Handling:</strong> Handle all transaction statuses</li>
        </ul>

        <h4>3.12.2 Contact Easebuzz</h4>
        <p>For merchant account setup, credentials, and support:</p>
        <ul>
            <li><strong>Email:</strong> pgsupport@easebuzz.in</li>
            <li><strong>Website:</strong> https://www.easebuzz.in</li>
            <li><strong>Documentation:</strong> Request complete API documentation</li>
            <li><strong>PHP SDK:</strong> Request download link for PHP library</li>
        </ul>

        <h4>3.12.3 xpaisa Easebuzz Callback Endpoints</h4>
        <p>Our system provides built-in Easebuzz callback endpoints:</p>
        <ul>
            <li><strong>Success URL (SURL):</strong> <code>https://merchant.xpaisa.in/api/gateway/easebuzz/callback</code></li>
            <li><strong>Failure URL (FURL):</strong> <code>https://merchant.xpaisa.in/api/gateway/easebuzz/callback</code></li>
            <li><strong>Webhook URL:</strong> <code>https://merchant.xpaisa.in/api/gateway/easebuzz/webhook</code></li>
        </ul>
        
        <p class="important-note"><strong>Important:</strong> These URLs must be whitelisted in your Easebuzz merchant dashboard.</p>

        <h3>3.13 Common Issues & Solutions</h3>
        <table>
            <thead>
                <tr>
                    <th>Issue</th>
                    <th>Solution</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Invalid hash</td>
                    <td>Verify hash sequence and ensure all UDF fields are included</td>
                </tr>
                <tr>
                    <td>Encryption error</td>
                    <td>Check KEY and IV generation, ensure proper padding</td>
                </tr>
                <tr>
                    <td>Access key expired</td>
                    <td>Access keys expire after 30 minutes - generate new one</td>
                </tr>
                <tr>
                    <td>Callback not received</td>
                    <td>Check SURL/FURL accessibility and webhook configuration</td>
                </tr>
                <tr>
                    <td>UPI timeout</td>
                    <td>Use webhooks for UPI - customer may not return to SURL</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="box p-4 mt-3">
        <h2>4. Wallet API</h2>

        <h3>4.1 Check Wallet Balance</h3>
        <p>Retrieve the current balances for your various wallets.</p>
        <h4>Endpoint:</h4>
        <pre><code>POST https://merchant.xpaisa.in/api/wallet</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>userid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your unique merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>token</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your API authentication token.</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "userid": "YOUR_USER_ID",
    "token": "YOUR_API_TOKEN"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": true,
    "message": "",
    "aeps": "1234.56",
    "payin": "7890.12",
    "payout": "345.00"
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": false,
    "message": "Userid invalid"
}</code></pre>
        <p class="important-note"><strong>Possible Failure Messages:</strong> "Userid invalid", "Invalid Token", "Userid Blocked".</p>                          
    </div>

    <div class="box p-4 mt-3">
        <h2>5. Card Payment API</h2>
        <p>Accept credit and debit card payments from your customers.</p>

        <h3>5.1 Initiate Card Payment</h3>
        <p>Process card payments (Credit Card, Debit Card) from customers.</p>
        
        <h4>Endpoint:</h4>
        <pre><code>POST https://merchant.xpaisa.in/api/card/initiate</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>token</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your API authentication token.</td>
                </tr>
                <tr>
                    <td><code>userid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your unique merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>Number</td>
                    <td>Yes</td>
                    <td>Payment amount (e.g., 100.00).</td>
                </tr>
                <tr>
                    <td><code>currency</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Currency code (INR, USD, EUR, etc.).</td>
                </tr>
                <tr>
                    <td><code>reference</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your unique order/transaction reference.</td>
                </tr>
                <tr>
                    <td><code>firstname</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's first name.</td>
                </tr>
                <tr>
                    <td><code>lastname</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's last name.</td>
                </tr>
                <tr>
                    <td><code>email</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's email address.</td>
                </tr>
                <tr>
                    <td><code>phone</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's phone number.</td>
                </tr>
                <tr>
                    <td><code>cardName</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Name on card (as printed on card).</td>
                </tr>
                <tr>
                    <td><code>cardNumber</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Card number (13-19 digits).</td>
                </tr>
                <tr>
                    <td><code>cardCVV</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Card CVV/CVC (3 or 4 digits).</td>
                </tr>
                <tr>
                    <td><code>expMonth</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Card expiry month (01-12).</td>
                </tr>
                <tr>
                    <td><code>expYear</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Card expiry year (YYYY format).</td>
                </tr>
                <tr>
                    <td><code>country</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Billing country code (e.g., "IN", "US").</td>
                </tr>
                <tr>
                    <td><code>city</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Billing city.</td>
                </tr>
                <tr>
                    <td><code>address</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Billing street address.</td>
                </tr>
                <tr>
                    <td><code>ip_address</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Customer's IP address.</td>
                </tr>
                <tr>
                    <td><code>zip_code</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Postal/ZIP code.</td>
                </tr>
                <tr>
                    <td><code>state</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Billing state/province.</td>
                </tr>
                <tr>
                    <td><code>callback_url</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Your URL to receive payment status updates.</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "token": "your_api_token",
    "userid": "your_merchant_id",
    "amount": 100.00,
    "currency": "INR",
    "reference": "ORDER12345",
    "firstname": "John",
    "lastname": "Doe",
    "email": "customer@example.com",
    "phone": "9876543210",
    "cardName": "JOHN DOE",
    "cardNumber": "4111111111111111",
    "cardCVV": "123",
    "expMonth": "12",
    "expYear": "2025",
    "country": "IN",
    "city": "Mumbai",
    "address": "123 Main Street",
    "ip_address": "192.168.1.1",
    "zip_code": "400001",
    "state": "Maharashtra",
    "callback_url": "https://yoursite.com/payment/callback"
}</code></pre>

        <h4>Example Success Response (Direct Payment):</h4>
        <pre><code>{
    "status": "success",
    "transaction_id": 12345,
    "order_id": "ORDER12345_1642234567",
    "message": "Card payment successful",
    "amount": 100.00,
    "currency": "INR"
}</code></pre>

        <h4>Example Success Response (3D Secure):</h4>
        <pre><code>{
    "status": "redirect",
    "transaction_id": 12345,
    "redirect_link": "https://bank-3ds.example.com/authenticate",
    "message": "3D Secure authentication required"
}</code></pre>

        <p class="important-note"><strong>3D Secure:</strong> When status is "redirect", redirect customer to the redirect_link to complete authentication. After authentication, you'll receive a callback with final payment status.</p>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": "failed",
    "message": "Card declined by issuer"
}</code></pre>

        <h3>5.2 Card Payment Callback</h3>
        <p>We will send payment status updates to your <code>callback_url</code> after processing.</p>

        <h4>Callback Payload:</h4>
        <pre><code>{
    "status": "success",
    "transaction_id": 12345,
    "reference": "ORDER12345",
    "order_id": "ORDER12345_1642234567",
    "amount": "100.00",
    "currency": "INR",
    "card_last4": "1111",
    "utr": "BANK_REF_NUMBER",
    "timestamp": "2024-01-15T10:30:45Z"
}</code></pre>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>status</code></td>
                    <td>String</td>
                    <td>Payment status: <code>success</code> or <code>failed</code>.</td>
                </tr>
                <tr>
                    <td><code>transaction_id</code></td>
                    <td>Number</td>
                    <td>xpaisa transaction ID.</td>
                </tr>
                <tr>
                    <td><code>reference</code></td>
                    <td>String</td>
                    <td>Your original order reference.</td>
                </tr>
                <tr>
                    <td><code>order_id</code></td>
                    <td>String</td>
                    <td>Payment gateway order ID.</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>String</td>
                    <td>Transaction amount.</td>
                </tr>
                <tr>
                    <td><code>currency</code></td>
                    <td>String</td>
                    <td>Currency code.</td>
                </tr>
                <tr>
                    <td><code>card_last4</code></td>
                    <td>String</td>
                    <td>Last 4 digits of card (only on success).</td>
                </tr>
                <tr>
                    <td><code>utr</code></td>
                    <td>String</td>
                    <td>Bank reference/UTR number (only on success).</td>
                </tr>
                <tr>
                    <td><code>timestamp</code></td>
                    <td>String</td>
                    <td>ISO 8601 timestamp of payment.</td>
                </tr>
            </tbody>
        </table>

        <p class="important-note"><strong>Important:</strong> Your callback URL must be publicly accessible. Always use callbacks to update order status - do not rely on customer returning to your site.</p>

        <h3>5.3 Security Guidelines</h3>
        <ul>
            <li>✅ Always use <strong>HTTPS</strong> for API calls</li>
            <li>✅ <strong>Never</strong> store full card numbers in your database</li>
            <li>✅ <strong>Never</strong> log card numbers or CVVs</li>
            <li>✅ Keep your API token secure - never expose in frontend code</li>
            <li>✅ Validate callback data matches your records</li>
            <li>✅ Implement PCI-DSS compliance for card data handling</li>
        </ul>

        <h3>5.4 Test Cards</h3>
        <p>Use these test card numbers in test environment:</p>
        <table>
            <thead>
                <tr>
                    <th>Card Type</th>
                    <th>Card Number</th>
                    <th>CVV</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Visa</td>
                    <td><code>4111111111111111</code></td>
                    <td>Any</td>
                    <td>Success</td>
                </tr>
                <tr>
                    <td>Mastercard</td>
                    <td><code>5555555555554444</code></td>
                    <td>Any</td>
                    <td>Success</td>
                </tr>
                <tr>
                    <td>Visa</td>
                    <td><code>4000000000000002</code></td>
                    <td>Any</td>
                    <td>Declined</td>
                </tr>
            </tbody>
        </table>
        <p><strong>Test Expiry:</strong> Any future date (e.g., 12/2025)</p>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // No specific JS for this page, it's purely for display.
        });
    </script>
@endsection