@extends('user.layout.NewUser')

@section('css')
<style>
    .payment-link-container {
        background: var(--card-bg);
        border-radius: var(--border-radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
    }
    
    .form-section {
        background: white;
        border-radius: var(--border-radius-md);
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .form-section h5 {
        color: var(--primary-orange);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        border: 1px solid #ddd;
        border-radius: var(--border-radius-md);
        padding: 0.75rem;
    }
    
    .form-control:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25);
    }
    
    .result-section {
        display: none;
        background: #f0f9ff;
        border: 2px solid var(--primary-orange);
        border-radius: var(--border-radius-md);
        padding: 2rem;
        margin-top: 2rem;
    }
    
    .result-section.show {
        display: block;
    }
    
    .result-section h5 {
        color: var(--primary-orange);
        margin-bottom: 1rem;
    }
    
    .payment-link-box {
        background: white;
        border: 2px dashed var(--primary-orange);
        border-radius: var(--border-radius-md);
        padding: 1.5rem;
        margin: 1rem 0;
    }
    
    .payment-link-box a {
        color: var(--primary-orange);
        font-size: 1.1rem;
        word-break: break-all;
        text-decoration: none;
    }
    
    .payment-link-box a:hover {
        text-decoration: underline;
    }
    
    .qr-code-container {
        text-align: center;
        margin: 1.5rem 0;
    }
    
    .qr-code-container img {
        max-width: 250px;
        border: 2px solid #ddd;
        border-radius: var(--border-radius-md);
        padding: 1rem;
        background: white;
    }
    
    .btn-generate {
        background: var(--primary-orange);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: var(--border-radius-md);
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s;
    }
    
    .btn-generate:hover {
        background: var(--dark-orange);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(241, 90, 34, 0.3);
    }
    
    .btn-generate:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .alert {
        border-radius: var(--border-radius-md);
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 1px solid #10b981;
        color: #065f46;
    }
    
    .alert-danger {
        background: #fee2e2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }
    
    .info-text {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-top: 0.25rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="payment-link-container">
                <h2 class="mb-4" style="color: var(--primary-orange); font-weight: 700;">
                    <i class="fas fa-link me-2"></i>Payment Link Generator
                </h2>
                
                <!-- Payment Link Form -->
                <div class="form-section">
                    <h5><i class="fas fa-credit-card me-2"></i>Generate Payment Link</h5>
                    
                    <div id="alertContainer"></div>
                    
                    <form id="paymentLinkForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount" id="amount" 
                                       placeholder="Enter amount (e.g., 100)" min="1" step="0.01" required>
                                <small class="info-text">Minimum amount: ₹1</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Mobile <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="mobile" id="mobile" 
                                       placeholder="Enter 10-digit mobile number" maxlength="10" pattern="[0-9]{10}" required>
                                <small class="info-text">10-digit mobile number</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" 
                                       placeholder="Enter customer name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="orderid" id="orderid" 
                                       placeholder="Enter unique order ID" minlength="15" maxlength="30" pattern="[a-zA-Z0-9]+" required>
                                <small class="info-text">15-30 characters, alphanumeric only</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Generate QR Code</label>
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="qr_required" id="qr_required" value="1">
                                    <label class="form-check-label" for="qr_required">
                                        Generate QR code along with payment link
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-generate" id="generateBtn">
                                <i class="fas fa-link me-2"></i>Generate Payment Link
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Result Section -->
                <div class="result-section" id="resultSection">
                    <h5><i class="fas fa-check-circle me-2"></i>Payment Link Generated Successfully!</h5>
                    
                    <div class="payment-link-box">
                        <strong>Payment Link:</strong>
                        <div class="mt-2">
                            <a href="#" id="paymentLink" target="_blank"></a>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyPaymentLink()">
                            <i class="fas fa-copy me-1"></i>Copy Link
                        </button>
                    </div>
                    
                    <div id="qrCodeContainer" class="qr-code-container" style="display: none;">
                        <h6>QR Code:</h6>
                        <img id="qrCodeImage" src="" alt="QR Code">
                    </div>
                    
                    <div class="mt-3">
                        <strong>Amount:</strong> ₹<span id="resultAmount"></span><br>
                        <strong>Service Charge:</strong> ₹<span id="resultTax"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Auto-generate order ID if empty (15-30 characters)
        $('#orderid').on('blur', function() {
            if (!$(this).val()) {
                const timestamp = Date.now().toString();
                const random = Math.random().toString(36).substring(2, 12).toUpperCase();
                const orderId = 'DK' + timestamp.substring(timestamp.length - 10) + random;
                // Ensure it's between 15-30 characters
                const finalOrderId = orderId.length > 30 ? orderId.substring(0, 30) : orderId;
                $(this).val(finalOrderId);
            }
        });
        
        // Form submission
        $('#paymentLinkForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const generateBtn = $('#generateBtn');
            const originalText = generateBtn.html();
            
            // Disable button and show loading
            generateBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
            
            // Hide previous results
            $('#resultSection').removeClass('show');
            $('#alertContainer').empty();
            
            // Get form data
            const formData = {
                amount: $('#amount').val(),
                mobile: $('#mobile').val(),
                name: $('#name').val(),
                orderid: $('#orderid').val(),
                qr_required: $('#qr_required').is(':checked') ? 1 : 0,
                _token: $('input[name="_token"]').val()
            };
            
            // Validate order ID
            if (formData.orderid.length < 15 || formData.orderid.length > 30) {
                showAlert('danger', 'Order ID must be between 15 and 30 characters');
                generateBtn.prop('disabled', false).html(originalText);
                return;
            }
            
            // Validate order ID format (alphanumeric only)
            if (!/^[a-zA-Z0-9]+$/.test(formData.orderid)) {
                showAlert('danger', 'Order ID must contain only letters and numbers');
                generateBtn.prop('disabled', false).html(originalText);
                return;
            }
            
            // Validate mobile
            if (formData.mobile.length !== 10 || !/^[0-9]+$/.test(formData.mobile)) {
                showAlert('danger', 'Mobile number must be exactly 10 digits');
                generateBtn.prop('disabled', false).html(originalText);
                return;
            }
            
            // Make API call
            $.ajax({
                url: '{{ route("user.payment_link.generate") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === true || response.status === 'true') {
                        // Show success
                        $('#paymentLink').attr('href', response.url).text(response.url);
                        $('#resultAmount').text(parseFloat(response.amount || 0).toFixed(2));
                        $('#resultTax').text(parseFloat(response.tax || 0).toFixed(2));
                        
                        // Show QR code if available
                        if (response.qr_code) {
                            $('#qrCodeImage').attr('src', response.qr_code);
                            $('#qrCodeContainer').show();
                        } else {
                            $('#qrCodeContainer').hide();
                        }
                        
                        $('#resultSection').addClass('show');
                        showAlert('success', response.message || 'Payment link generated successfully!');
                        
                        // Scroll to result
                        $('html, body').animate({
                            scrollTop: $('#resultSection').offset().top - 100
                        }, 500);
                    } else {
                        showAlert('danger', response.message || 'Failed to generate payment link');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'An error occurred while generating payment link';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showAlert('danger', errorMsg);
                },
                complete: function() {
                    generateBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = $(button).html();
            $(button).html('<i class="fas fa-check"></i> Copied!');
            setTimeout(function() {
                $(button).html(originalHtml);
            }, 2000);
        });
    }
    
    function copyPaymentLink() {
        const link = $('#paymentLink').attr('href');
        if (link) {
            copyToClipboard(link, event.target);
        }
    }
</script>
@endsection


@section('css')
<style>
    .payment-link-container {
        background: var(--card-bg);
        border-radius: var(--border-radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
    }
    
    .form-section {
        background: white;
        border-radius: var(--border-radius-md);
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .form-section h5 {
        color: var(--primary-orange);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        border: 1px solid #ddd;
        border-radius: var(--border-radius-md);
        padding: 0.75rem;
    }
    
    .form-control:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25);
    }
    
    .result-section {
        display: none;
        background: #f0f9ff;
        border: 2px solid var(--primary-orange);
        border-radius: var(--border-radius-md);
        padding: 2rem;
        margin-top: 2rem;
    }
    
    .result-section.show {
        display: block;
    }
    
    .result-section h5 {
        color: var(--primary-orange);
        margin-bottom: 1rem;
    }
    
    .payment-link-box {
        background: white;
        border: 2px dashed var(--primary-orange);
        border-radius: var(--border-radius-md);
        padding: 1.5rem;
        margin: 1rem 0;
    }
    
    .payment-link-box a {
        color: var(--primary-orange);
        font-size: 1.1rem;
        word-break: break-all;
        text-decoration: none;
    }
    
    .payment-link-box a:hover {
        text-decoration: underline;
    }
    
    .qr-code-container {
        text-align: center;
        margin: 1.5rem 0;
    }
    
    .qr-code-container img {
        max-width: 250px;
        border: 2px solid #ddd;
        border-radius: var(--border-radius-md);
        padding: 1rem;
        background: white;
    }
    
    .btn-generate {
        background: var(--primary-orange);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: var(--border-radius-md);
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s;
    }
    
    .btn-generate:hover {
        background: var(--dark-orange);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(241, 90, 34, 0.3);
    }
    
    .btn-generate:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .alert {
        border-radius: var(--border-radius-md);
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 1px solid #10b981;
        color: #065f46;
    }
    
    .alert-danger {
        background: #fee2e2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }
    
    .info-text {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-top: 0.25rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="payment-link-container">
                <h2 class="mb-4" style="color: var(--primary-orange); font-weight: 700;">
                    <i class="fas fa-link me-2"></i>Payment Link Generator
                </h2>
                
                <!-- Payment Link Form -->
                <div class="form-section">
                    <h5><i class="fas fa-credit-card me-2"></i>Generate Payment Link</h5>
                    
                    <div id="alertContainer"></div>
                    
                    <form id="paymentLinkForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount" id="amount" 
                                       placeholder="Enter amount (e.g., 100)" min="1" step="0.01" required>
                                <small class="info-text">Minimum amount: ₹1</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Mobile <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="mobile" id="mobile" 
                                       placeholder="Enter 10-digit mobile number" maxlength="10" pattern="[0-9]{10}" required>
                                <small class="info-text">10-digit mobile number</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" 
                                       placeholder="Enter customer name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="orderid" id="orderid" 
                                       placeholder="Enter unique order ID" minlength="15" maxlength="30" pattern="[a-zA-Z0-9]+" required>
                                <small class="info-text">15-30 characters, alphanumeric only</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Generate QR Code</label>
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="qr_required" id="qr_required" value="1">
                                    <label class="form-check-label" for="qr_required">
                                        Generate QR code along with payment link
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-generate" id="generateBtn">
                                <i class="fas fa-link me-2"></i>Generate Payment Link
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Result Section -->
                <div class="result-section" id="resultSection">
                    <h5><i class="fas fa-check-circle me-2"></i>Payment Link Generated Successfully!</h5>
                    
                    <div class="payment-link-box">
                        <strong>Payment Link:</strong>
                        <div class="mt-2">
                            <a href="#" id="paymentLink" target="_blank"></a>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyPaymentLink()">
                            <i class="fas fa-copy me-1"></i>Copy Link
                        </button>
                    </div>
                    
                    <div id="qrCodeContainer" class="qr-code-container" style="display: none;">
                        <h6>QR Code:</h6>
                        <img id="qrCodeImage" src="" alt="QR Code">
                    </div>
                    
                    <div class="mt-3">
                        <strong>Amount:</strong> ₹<span id="resultAmount"></span><br>
                        <strong>Service Charge:</strong> ₹<span id="resultTax"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Auto-generate order ID if empty (15-30 characters)
        $('#orderid').on('blur', function() {
            if (!$(this).val()) {
                const timestamp = Date.now().toString();
                const random = Math.random().toString(36).substring(2, 12).toUpperCase();
                const orderId = 'DK' + timestamp.substring(timestamp.length - 10) + random;
                // Ensure it's between 15-30 characters
                const finalOrderId = orderId.length > 30 ? orderId.substring(0, 30) : orderId;
                $(this).val(finalOrderId);
            }
        });
        
        // Form submission
        $('#paymentLinkForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const generateBtn = $('#generateBtn');
            const originalText = generateBtn.html();
            
            // Disable button and show loading
            generateBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
            
            // Hide previous results
            $('#resultSection').removeClass('show');
            $('#alertContainer').empty();
            
            // Get form data
            const formData = {
                amount: $('#amount').val(),
                mobile: $('#mobile').val(),
                name: $('#name').val(),
                orderid: $('#orderid').val(),
                qr_required: $('#qr_required').is(':checked') ? 1 : 0,
                _token: $('input[name="_token"]').val()
            };
            
            // Validate order ID
            if (formData.orderid.length < 15 || formData.orderid.length > 30) {
                showAlert('danger', 'Order ID must be between 15 and 30 characters');
                generateBtn.prop('disabled', false).html(originalText);
                return;
            }
            
            // Validate order ID format (alphanumeric only)
            if (!/^[a-zA-Z0-9]+$/.test(formData.orderid)) {
                showAlert('danger', 'Order ID must contain only letters and numbers');
                generateBtn.prop('disabled', false).html(originalText);
                return;
            }
            
            // Validate mobile
            if (formData.mobile.length !== 10 || !/^[0-9]+$/.test(formData.mobile)) {
                showAlert('danger', 'Mobile number must be exactly 10 digits');
                generateBtn.prop('disabled', false).html(originalText);
                return;
            }
            
            // Make API call
            $.ajax({
                url: '{{ route("user.payment_link.generate") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === true || response.status === 'true') {
                        // Show success
                        $('#paymentLink').attr('href', response.url).text(response.url);
                        $('#resultAmount').text(parseFloat(response.amount || 0).toFixed(2));
                        $('#resultTax').text(parseFloat(response.tax || 0).toFixed(2));
                        
                        // Show QR code if available
                        if (response.qr_code) {
                            $('#qrCodeImage').attr('src', response.qr_code);
                            $('#qrCodeContainer').show();
                        } else {
                            $('#qrCodeContainer').hide();
                        }
                        
                        $('#resultSection').addClass('show');
                        showAlert('success', response.message || 'Payment link generated successfully!');
                        
                        // Scroll to result
                        $('html, body').animate({
                            scrollTop: $('#resultSection').offset().top - 100
                        }, 500);
                    } else {
                        showAlert('danger', response.message || 'Failed to generate payment link');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'An error occurred while generating payment link';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showAlert('danger', errorMsg);
                },
                complete: function() {
                    generateBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = $(button).html();
            $(button).html('<i class="fas fa-check"></i> Copied!');
            setTimeout(function() {
                $(button).html(originalHtml);
            }, 2000);
        });
    }
    
    function copyPaymentLink() {
        const link = $('#paymentLink').attr('href');
        if (link) {
            copyToClipboard(link, event.target);
        }
    }
</script>
@endsection

