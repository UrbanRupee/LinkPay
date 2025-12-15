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
            color: #FFFFFF;
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
            color: #FFFFFF;
        }
        .api-docs-container ul {
            list-style-type: disc;
            margin-left: 1.5rem;
            margin-bottom: 1rem;
            color: #FFFFFF;
        }
        .api-docs-container ul li {
            margin-bottom: 0.5rem;
            color: #FFFFFF;
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
    </style>
@endsection

@section('content')
<div class="container-fluid api-docs-container" style="background: var(--black); padding: 2rem; min-height: 100vh;">
    <div class="box p-4">
        <h1>xpaisa API Documentation</h1>
        <p>Welcome to the xpaisa API documentation. This guide provides details on integrating with our payment gateway for PayIn, PayOut, Card Payments, and Wallet operations.</p>

        <h2>General Information</h2>
        <ul>
            <li>All API requests should be sent as <strong>JSON</strong> in the request body.</li>
            <li>All responses will be in <strong>JSON</strong> format.</li>
            <li><strong>Base URL:</strong> <code>https://merchant.xpaisa.in/api/</code></li>
            <li><strong>Authentication:</strong> All requests require your <code>token</code> and <code>userid</code></li>
        </ul>
    </div>

    <!-- 1. PAYIN API -->
    <div class="box p-4 mt-3">
        <h2>1. PayIn API</h2>
        <p>Collect payments from your customers via UPI, Cards, Net Banking, and Wallets.</p>

        <h3>1.1 Initiate PayIn</h3>
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
                    <td>Amount to be paid (e.g., "100" for ₹100.00).</td>
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
                    <td>Unique order ID (exactly 20 characters, alphanumeric only).</td>
                </tr>
                <tr>
                    <td><code>callback_url</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>URL to receive payment status updates (POST).</td>
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
    "url": "upi://pay?pa=merchant@upi&pn=XPAISA&tr=TXN123&am=100.0"
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": false,
    "message": "Error message detailing the failure"
}</code></pre>

        <h3>1.2 PayIn Callback</h3>
        <p>xpaisa will send payment status updates to your <code>callback_url</code> after processing.</p>

        <h4>Callback Payload:</h4>
        <pre><code>{
    "status": "success",
    "client_txn_id": "xpaisaPayin00001234",
    "utr": "409196790567",
    "amount": "100.00"
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
                    <td><code>client_txn_id</code></td>
                    <td>String</td>
                    <td>Your orderid from initiation request.</td>
                </tr>
                <tr>
                    <td><code>utr</code></td>
                    <td>String</td>
                    <td>Unique Transaction Reference from bank.</td>
                </tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>String</td>
                    <td>Transaction amount.</td>
                </tr>
            </tbody>
        </table>

        <p class="important-note"><strong>Important:</strong> Callback URL must be publicly accessible and handle POST requests.</p>

        <h3>1.3 Check PayIn Status</h3>
        <p>Check the current status of a PayIn transaction.</p>

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
                    <td>Your merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>orderid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>The order ID from your system.</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "userid": "MP1001234",
    "orderid": "xpaisaPayin00001234"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": "success",
    "message": "",
    "utr": "491850579611",
    "client_txn_id": "xpaisaPayin00001234"
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": "failed",
    "message": "Invalid Order Id!!"
}</code></pre>
    </div>

    <!-- 2. PAYOUT API -->
    <div class="box p-4 mt-3">
        <h2>2. PayOut API</h2>
        <p>Send funds to beneficiaries via bank transfer or UPI.</p>

        <h3>2.1 Initiate PayOut</h3>
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
                    <td>Beneficiary's bank account number.</td>
                </tr>
                <tr>
                    <td><code>ifsc</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>Bank IFSC code.</td>
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
    "amount": "1000",
    "mobile": "9876543210",
    "name": "BENEFICIARY NAME",
    "number": "1234567890123456",
    "ifsc": "SBIN0001234",
    "orderid": "PAYOUT20240115001"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": true,
    "message": "Success",
    "id": "RDXPAY_142835150125123",
    "dataByBank": true
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": false,
    "message": "Insufficient Fund!!",
    "id": ""
}</code></pre>

        <p class="important-note"><strong>Important:</strong> Contact support to whitelist your IP addresses for payout initiation.</p>

        <h3>2.2 PayOut Callback</h3>
        <p>xpaisa will send payout status updates to your configured callback URL.</p>

        <h4>Callback Payload:</h4>
        <pre><code>{
    "status": "success",
    "remark": "Funds successfully disbursed.",
    "transaction_id": "RDXPAY_142835150125123",
    "utr": "688148704280"
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
                    <td>Payout status: <code>success</code> or <code>refund</code> (failed).</td>
                </tr>
                <tr>
                    <td><code>remark</code></td>
                    <td>String</td>
                    <td>Message or reason for the status.</td>
                </tr>
                <tr>
                    <td><code>transaction_id</code></td>
                    <td>String</td>
                    <td>Transaction ID generated by xpaisa.</td>
                </tr>
                <tr>
                    <td><code>utr</code></td>
                    <td>String</td>
                    <td>Bank reference/UTR number.</td>
                </tr>
            </tbody>
        </table>

        <h3>2.3 Check PayOut Status</h3>
        <p>Check the current status of a PayOut transaction.</p>

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
                    <td>Your merchant User ID.</td>
                </tr>
                <tr>
                    <td><code>orderid</code></td>
                    <td>String</td>
                    <td>Yes</td>
                    <td>The order ID from your system.</td>
                </tr>
            </tbody>
        </table>

        <h4>Example Request:</h4>
        <pre><code>{
    "userid": "MP1001234",
    "orderid": "PAYOUT20240115001"
}</code></pre>

        <h4>Example Success Response:</h4>
        <pre><code>{
    "status": "success",
    "message": "",
    "utr": "CMP00000000009395684",
    "client_txn_id": "PAYOUT20240115001"
}</code></pre>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": "failed",
    "message": "Invalid Order Id!!"
}</code></pre>
    </div>

    <!-- 3. WALLET API -->
    <div class="box p-4 mt-3">
        <h2>3. Wallet API</h2>

        <h3>3.1 Check Wallet Balance</h3>
        <p>Retrieve your current wallet balances.</p>
        
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

        <p class="important-note"><strong>Possible Errors:</strong> "Userid invalid", "Invalid Token", "Userid Blocked"</p>
    </div>

    <!-- 4. CARD PAYMENT API -->
    <div class="box p-4 mt-3">
        <h2>4. Card Payment API</h2>
        <p>Accept credit and debit card payments from your customers.</p>

        <h3>4.1 Initiate Card Payment</h3>
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
    "redirect_link": "https://secure.xpaisa.in/3ds/authenticate",
    "message": "3D Secure authentication required"
}</code></pre>

        <p class="important-note"><strong>3D Secure:</strong> When status is "redirect", redirect customer to the redirect_link to complete authentication. You'll receive a callback with final payment status.</p>

        <h4>Example Failure Response:</h4>
        <pre><code>{
    "status": "failed",
    "message": "Card declined by issuer"
}</code></pre>

        <h3>4.2 Card Payment Callback</h3>
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

        <p class="important-note"><strong>Important:</strong> Your callback URL must be publicly accessible. Always use callbacks to update order status.</p>

        <h3>4.3 Security Guidelines</h3>
        <ul>
            <li>✅ Always use <strong>HTTPS</strong> for API calls</li>
            <li>✅ <strong>Never</strong> store full card numbers in your database</li>
            <li>✅ <strong>Never</strong> log card numbers or CVVs</li>
            <li>✅ Keep your API token secure - never expose in frontend code</li>
            <li>✅ Validate callback data matches your records</li>
            <li>✅ Implement PCI-DSS compliance for card data handling</li>
        </ul>

        <h3>4.4 Test Cards</h3>
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

    <!-- SUPPORT SECTION -->
    <div class="box p-4 mt-3">
        <h2>5. Support & Activation</h2>
        
        <h3>Getting Started</h3>
        <ul>
            <li>📧 <strong>Email:</strong> support@xpaisa.in</li>
            <li>🌐 <strong>Website:</strong> https://merchant.xpaisa.in</li>
            <li>💬 <strong>Live Chat:</strong> Available in merchant dashboard</li>
        </ul>

        <h3>Activation Steps</h3>
        <ol>
            <li>Contact xpaisa support to activate payment services</li>
            <li>Receive your <code>userid</code> and <code>token</code> credentials</li>
            <li>Test integration using test cards/accounts</li>
            <li>Go live after successful testing</li>
        </ol>

        <p class="important-note"><strong>Need Help?</strong> Contact our support team for API activation, technical assistance, or integration questions.</p>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize Lucide icons
            lucide.createIcons();
        });
    </script>
@endsection







