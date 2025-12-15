@extends('admin.layout.user')
@section('css')
<style>
    .provider-form-card {
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(241, 90, 34, 0.15);
        border: 2px solid var(--primary-orange);
    }
    .provider-form-card .card-header {
        background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
        color: white;
        border-radius: 10px 10px 0 0;
        padding: 1.5rem;
    }
    .provider-form-card .form-label {
        color: var(--text-dark);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .provider-form-card .form-control {
        border: 2px solid rgba(241, 90, 34, 0.2);
        border-radius: 8px;
        padding: 0.75rem;
        color: var(--text-dark);
    }
    .provider-form-card .form-control:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.15);
    }
    .btn-save-provider {
        background: linear-gradient(135deg, #10B981, #059669);
        border: none;
        color: white !important;
        padding: 1rem 3rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    .btn-save-provider:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
        color: white !important;
    }
    .btn-view-providers {
        background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
        border: none;
        color: white !important;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-view-providers:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(241, 90, 34, 0.4);
        color: white !important;
    }
    .info-box {
        background: rgba(241, 90, 34, 0.08);
        border-left: 4px solid var(--primary-orange);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .providers-list-card {
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(241, 90, 34, 0.15);
        margin-top: 2rem;
    }
    .provider-item {
        border: 2px solid rgba(241, 90, 34, 0.2);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }
    .provider-item:hover {
        border-color: var(--primary-orange);
        box-shadow: 0 4px 12px rgba(241, 90, 34, 0.2);
    }
</style>
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Providers Management</li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-lg-10 col-md-12 mx-auto">
                <div class="card provider-form-card">
                    <div class="card-header">
                        <h4 class="mb-0" style="color: white; font-weight: 700;">
                            <i data-lucide="plus-circle" style="width: 24px; height: 24px; margin-right: 0.5rem;"></i>
                            Add New Provider
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 2rem;">
                        
                        <!-- Info Box -->
                        <div class="info-box">
                            <div style="display: flex; align-items: start;">
                                <i data-lucide="info" style="width: 20px; height: 20px; color: var(--primary-orange); margin-right: 0.75rem; flex-shrink: 0; margin-top: 0.125rem;"></i>
                                <p style="margin: 0; font-size: 0.9rem; color: var(--text-dark); line-height: 1.5;">
                                    <strong style="color: var(--primary-orange);">Note:</strong> Add payment gateway provider credentials here. Currently, only <strong>Easebuzz (Gateway ID: 28)</strong> is active for both PayIn and Payout.
                                </p>
                            </div>
                        </div>

                        <!-- Add Provider Form -->
                        <form id="addProviderForm" method="POST" action="{{ url('/admin/api/provider/add') }}">
                            @csrf
                            
        <div class="row">
                                <!-- Provider Name -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Provider Name *</label>
                                    <input type="text" name="provider_name" class="form-control" placeholder="e.g., Easebuzz" required>
                                </div>

                                <!-- Gateway ID -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gateway ID *</label>
                                    <select name="gateway_id" class="form-control" required>
                                        <option value="">-- Select Gateway --</option>
                                        <option value="28" selected>28 - Easebuzz (Active)</option>
                                        <option value="100">100 - No Gateway</option>
                                    </select>
                                </div>

                                <!-- Gateway Type -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gateway Type *</label>
                                    <select name="gateway_type" class="form-control" required>
                                        <option value="">-- Select Type --</option>
                                        <option value="payin">PayIn</option>
                                        <option value="payout">Payout</option>
                                        <option value="both" selected>Both (PayIn & Payout)</option>
                                    </select>
                                </div>

                                <!-- API Key / Merchant Key -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">API Key / Merchant Key *</label>
                                    <input type="text" name="api_key" class="form-control" placeholder="e.g., AEFQ63QEFK" required>
                                </div>

                                <!-- API Secret / Salt -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">API Secret / Salt *</label>
                                    <input type="text" name="api_secret" class="form-control" placeholder="e.g., BMHVGJZTOJ" required>
                                </div>

                                <!-- Merchant ID (Optional) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Merchant ID</label>
                                    <input type="text" name="merchant_id" class="form-control" placeholder="e.g., 244425">
                                </div>

                                <!-- Environment -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Environment *</label>
                                    <select name="environment" class="form-control" required>
                                        <option value="test">Test / UAT</option>
                                        <option value="prod" selected>Production</option>
                                    </select>
                                </div>

                                <!-- Callback URL -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Callback URL</label>
                                    <input type="url" name="callback_url" class="form-control" placeholder="https://merchant.xpaisa.in/api/gateway/easebuzz/callback">
                                </div>

                                <!-- Webhook URL -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Webhook URL</label>
                                    <input type="url" name="webhook_url" class="form-control" placeholder="https://merchant.xpaisa.in/api/gateway/easebuzz/webhook">
                                </div>

                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-control" required>
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>

                                <!-- Priority -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Priority</label>
                                    <input type="number" name="priority" class="form-control" placeholder="1" value="1" min="1">
                                </div>

                                <!-- Notes -->
                                <div class="col-md-12 mb-4">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes or configuration details..."></textarea>
                                </div>
                            </div>

                            <!-- BIG SAVE BUTTON -->
                            <div class="text-center" style="margin-top: 2rem; padding: 1.5rem; background: rgba(16, 185, 129, 0.08); border-radius: 12px;">
                                <button type="submit" class="btn btn-save-provider">
                                    <i data-lucide="save" style="width: 22px; height: 22px; margin-right: 0.75rem;"></i>
                                    💾 SAVE PROVIDER
                                                        </button>
                                <p style="margin-top: 1rem; margin-bottom: 0; font-size: 0.9rem; color: var(--text-light);">
                                    Click here to save this provider. You can add multiple providers!
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- VIEW ALL PROVIDERS SECTION -->
                <div class="card providers-list-card" style="margin-top: 3rem;">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange)); color: white; padding: 1.5rem; border-radius: 10px 10px 0 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h4 class="mb-0" style="color: white; font-weight: 700;">
                                <i data-lucide="list" style="width: 24px; height: 24px; margin-right: 0.5rem;"></i>
                                All Added Providers
                            </h4>
                            <button onclick="loadProviders()" class="btn btn-sm" style="background: white; color: var(--primary-orange); font-weight: 600;">
                                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                                Refresh
                                                        </button>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 2rem;">
                        <div id="providersList">
                            <!-- Providers will load here -->
                            <div class="text-center" style="padding: 3rem;">
                                <i data-lucide="inbox" style="width: 64px; height: 64px; color: rgba(241, 90, 34, 0.3); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-light); font-size: 1.1rem;">Loading providers...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }

    // Load providers list
    function loadProviders() {
        const providersList = document.getElementById('providersList');
        
        providersList.innerHTML = `
            <div class="text-center" style="padding: 3rem;">
                <div class="spinner-border text-primary" role="status" style="color: var(--primary-orange) !important;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p style="color: var(--text-light); margin-top: 1rem;">Loading providers...</p>
            </div>
        `;
        
        // Fetch providers from database
        fetch('{{ url("/admin/api/providers/list") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status && data.providers && data.providers.length > 0) {
                    let html = '';
                    data.providers.forEach((provider, index) => {
                        const statusBadge = provider.status == 1 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        const gatewayType = provider.gateway_type || 'both';
                        const typeBadge = gatewayType === 'both' 
                            ? '<span class="badge" style="background: var(--primary-orange);">PayIn & Payout</span>'
                            : gatewayType === 'payin'
                            ? '<span class="badge bg-info">PayIn Only</span>'
                            : '<span class="badge bg-warning">Payout Only</span>';
                        
                        html += `
                            <div class="provider-item">
                                <div class="row align-items-center">
                                    <div class="col-md-1 text-center">
                                        <h3 style="color: var(--primary-orange); margin: 0; font-weight: 700;">${index + 1}</h3>
                                    </div>
                                    <div class="col-md-7">
                                        <h5 style="color: var(--text-dark); margin-bottom: 0.5rem; font-weight: 700;">
                                            ${provider.provider_name || 'Unnamed Provider'}
                                        </h5>
                                        <p style="margin-bottom: 0.25rem; color: var(--text-light); font-size: 0.9rem;">
                                            <strong>Gateway ID:</strong> ${provider.gateway_id} | 
                                            <strong>Environment:</strong> ${provider.environment || 'prod'}
                                        </p>
                                        <p style="margin-bottom: 0; color: var(--text-light); font-size: 0.85rem;">
                                            <strong>API Key:</strong> ${provider.api_key ? provider.api_key.substring(0, 8) + '...' : 'N/A'}
                                        </p>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        ${typeBadge}
                                    </div>
                                    <div class="col-md-2 text-center">
                                        ${statusBadge}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    providersList.innerHTML = html;
                } else {
                    providersList.innerHTML = `
                        <div class="text-center" style="padding: 3rem;">
                            <i data-lucide="inbox" style="width: 64px; height: 64px; color: rgba(241, 90, 34, 0.3); margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-light); font-size: 1.1rem;">No providers added yet</p>
                            <p style="color: var(--text-light); font-size: 0.9rem;">Add your first provider using the form above!</p>
                        </div>
                    `;
                }
                
                // Re-render icons
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            })
            .catch(error => {
                console.error('Error loading providers:', error);
                providersList.innerHTML = `
                    <div class="text-center" style="padding: 3rem;">
                        <i data-lucide="alert-triangle" style="width: 64px; height: 64px; color: #EF4444; margin-bottom: 1rem;"></i>
                        <p style="color: #EF4444; font-size: 1.1rem;">Error loading providers</p>
                        <p style="color: var(--text-light); font-size: 0.9rem;">${error.message}</p>
                    </div>
                `;
                
                // Re-render icons
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });
    }

    // Load providers on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadProviders();
    });

    // Form submission handler
    document.getElementById('addProviderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading
        Swal.fire({
            title: '💾 Saving Provider...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit form
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                Swal.fire({
                    icon: 'success',
                    title: '✅ Success!',
                    text: data.message || 'Provider saved successfully! You can add more.',
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'Great!'
                }).then(() => {
                    // Reset form
                    document.getElementById('addProviderForm').reset();
                    // Reload providers list
                    loadProviders();
                    // Scroll to providers list
                    document.getElementById('providersList').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '❌ Error!',
                    text: data.message || 'Failed to save provider',
                    confirmButtonColor: '#F15A22'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: '❌ Error!',
                text: 'Network error: ' + error.message,
                confirmButtonColor: '#F15A22'
            });
        });
    });
</script>
@endsection
