@extends('user.layout.NewUser')

@section('css')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    /* Enhanced Dashboard Styles - Orange & Cream Theme */
    :root {
        --primary-orange: #F15A22;
        --dark-orange: #D14A15;
        --light-orange: #FFEDD5;
        --white: #FFFFFF;
        --cream: #FFEDD5;
        --text-dark: #1F2937;
        --text-light: #6B7280;
        --border-color: rgba(241, 90, 34, 0.2);
        --card-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .page-content {
        background: var(--cream);
        padding: 1.5rem !important;
    }
    
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        background: var(--white);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-orange);
        border: 1px solid var(--border-color);
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(241, 90, 34, 0.3);
        border-color: var(--primary-orange);
    }
    
    .stat-card.success { border-left-color: var(--primary-orange); }
    .stat-card.danger { border-left-color: #DC2626; }
    .stat-card.warning { border-left-color: #F59E0B; }
    .stat-card.info { border-left-color: var(--primary-orange); }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
        background: rgba(241, 90, 34, 0.1);
        border: 2px solid var(--primary-orange);
    }
    
    .stat-card.success .stat-icon { background: rgba(241, 90, 34, 0.1); border-color: var(--primary-orange); }
    .stat-card.danger .stat-icon { background: rgba(220, 38, 38, 0.1); border-color: #DC2626; }
    .stat-card.warning .stat-icon { background: rgba(245, 158, 11, 0.1); border-color: #F59E0B; }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--text-light);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }
    
    .stat-change {
        font-size: 0.75rem;
        color: var(--primary-orange);
    }
    
    /* Chart Section */
    .chart-section {
        background: var(--white);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-orange);
    }
    
    .chart-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-dark);
    }
    
    .chart-container {
        position: relative;
        height: 350px;
    }
    
    /* Wallet Section */
    .wallet-card {
        background: var(--white);
        color: var(--text-dark);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(241, 90, 34, 0.15);
        margin-bottom: 1.5rem;
        border: 2px solid var(--primary-orange);
    }
    
    .wallet-title {
        font-size: 1rem;
        color: var(--primary-orange);
        margin-bottom: 1.5rem;
        font-weight: 700;
    }
    
    .wallet-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
    }
    
    .wallet-item h3 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: var(--text-dark);
    }
    
    .wallet-item p {
        font-size: 0.875rem;
        color: var(--text-light);
    }
    
    /* Transaction Table */
    .transactions-card {
        background: var(--white);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
    }
    
    .transactions-card h5 {
        color: var(--text-dark);
    }
    
    .table-modern {
        font-size: 0.875rem;
        color: var(--text-dark);
    }
    
    .table-modern thead th {
        background: var(--cream);
        color: var(--primary-orange);
        font-weight: 700;
        padding: 0.75rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .table-modern tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-dark);
    }
    
    .badge-success { background: var(--primary-orange); color: white; }
    .badge-danger { background: #DC2626; color: white; }
    .badge-warning { background: #F59E0B; color: white; }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .chart-container {
            height: 250px;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">

    <!-- Welcome Message -->
    @if (setting('welcome') != '')
    <div class="alert alert-info mb-4" style="background: var(--white); border: 2px solid var(--primary-orange); color: var(--text-dark);">
        <i data-lucide="megaphone" style="width:20px; height:20px; color: var(--primary-orange);"></i>
        <strong>{{ setting('welcome') }}</strong>
    </div>
    @endif

    <!-- API Credentials Card -->
    <div class="api-credentials-card" style="background: var(--white); border-radius: 16px; padding: 2rem; box-shadow: 0 10px 30px rgba(241, 90, 34, 0.15); margin-bottom: 1.5rem; border: 2px solid var(--primary-orange);">
        <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
            <i data-lucide="key" style="width: 24px; height: 24px; color: var(--primary-orange); margin-right: 0.75rem;"></i>
            <h6 style="font-size: 1rem; color: var(--primary-orange); margin: 0; font-weight: 700;">🔑 Your API Credentials</h6>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- User ID -->
            <div style="background: var(--cream); border-radius: 12px; padding: 1.25rem; border: 1px solid rgba(241, 90, 34, 0.2);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <label style="font-size: 0.875rem; color: var(--text-light); font-weight: 600;">User ID</label>
                    <button onclick="copyToClipboard('userid', '{{ user('userid') }}')" style="background: transparent; border: none; cursor: pointer; color: var(--primary-orange); padding: 0.25rem;">
                        <i data-lucide="copy" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
                <div style="display: flex; align-items: center;">
                    <input type="text" id="userid" value="{{ user('userid') }}" readonly style="flex: 1; background: white; border: 1px solid rgba(241, 90, 34, 0.3); border-radius: 8px; padding: 0.75rem; color: var(--text-dark); font-weight: 600; font-family: monospace; font-size: 0.95rem;">
                </div>
            </div>

            <!-- API Token -->
            <div style="background: var(--cream); border-radius: 12px; padding: 1.25rem; border: 1px solid rgba(241, 90, 34, 0.2);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <label style="font-size: 0.875rem; color: var(--text-light); font-weight: 600;">API Token</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="toggleTokenVisibility()" style="background: transparent; border: none; cursor: pointer; color: var(--primary-orange); padding: 0.25rem;">
                            <i data-lucide="eye" id="eye-icon" style="width: 16px; height: 16px;"></i>
                        </button>
                        <button onclick="copyToClipboard('token', '{{ user('token') }}')" style="background: transparent; border: none; cursor: pointer; color: var(--primary-orange); padding: 0.25rem;">
                            <i data-lucide="copy" style="width: 16px; height: 16px;"></i>
                        </button>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <input type="password" id="token" value="{{ user('token') }}" readonly style="flex: 1; background: white; border: 1px solid rgba(241, 90, 34, 0.3); border-radius: 8px; padding: 0.75rem; color: var(--text-dark); font-weight: 600; font-family: monospace; font-size: 0.95rem;">
                </div>
            </div>
        </div>

        <!-- Important Note -->
        <div style="background: rgba(241, 90, 34, 0.08); border-left: 4px solid var(--primary-orange); padding: 1rem 1.25rem; margin-top: 1.5rem; border-radius: 8px;">
            <div style="display: flex; align-items: start;">
                <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: var(--primary-orange); margin-right: 0.75rem; flex-shrink: 0; margin-top: 0.125rem;"></i>
                <p style="margin: 0; font-size: 0.875rem; color: var(--text-dark); line-height: 1.5;">
                    <strong style="color: var(--primary-orange);">Security Notice:</strong> Never share your API credentials with anyone. Keep them secure and use them only in your server-side code. View the <a href="/user/pgdocs" style="color: var(--primary-orange); text-decoration: underline; font-weight: 600;">API Documentation</a> for integration details.
                </p>
            </div>
        </div>
    </div>

    <!-- Wallet Balances Card -->
    <div class="wallet-card">
        <h6 class="wallet-title">💰 Your Wallet Balances</h6>
        <div class="wallet-grid">
            <div class="wallet-item">
                <h3>₹{{ number_format($currentPayinWallet ?? 0, 2) }}</h3>
                <p>Pay-in Wallet</p>
                </div>
            <div class="wallet-item">
                <h3>₹{{ number_format($currentPayoutWallet ?? 0, 2) }}</h3>
                <p>Pay-out Wallet</p>
                    </div>
            <div class="wallet-item">
                <h3>₹{{ number_format($currentHoldWallet ?? 0, 2) }}</h3>
                <p>Hold Wallet</p>
                    </div>
                    </div>
                </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i data-lucide="trending-up" style="width:24px; height:24px; color: #F15A22;"></i>
                        </div>
            <div class="stat-label">Total Successful Pay-in</div>
            <div class="stat-value">₹{{ number_format($totalPayinSuccess ?? 0, 2) }}</div>
                    </div>
        
        <div class="stat-card info">
            <div class="stat-icon">
                <i data-lucide="arrow-down-circle" style="width:24px; height:24px; color: #F15A22;"></i>
                        </div>
            <div class="stat-label">Total Successful Pay-out</div>
            <div class="stat-value">₹{{ number_format($totalPayoutSuccess ?? 0, 2) }}</div>
                    </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i data-lucide="activity" style="width:24px; height:24px; color: #FCA5A5;"></i>
            </div>
            <div class="stat-label">Today's Successful</div>
            <div class="stat-value">{{ $tsuccesscount ?? 0 }}</div>
        </div>

                    <div class="stat-card">
            <div class="stat-icon">
                <i data-lucide="clock" style="width:24px; height:24px; color: #991B1B;"></i>
                        </div>
            <div class="stat-label">Today's Pending</div>
            <div class="stat-value">{{ $tpendingcount ?? 0 }}</div>
                        </div>
                    </div>

    <!-- Payin Analytics Section -->
    <div class="chart-section">
        <div class="chart-header">
            <h6 class="chart-title">📊 Pay-in Analytics</h6>
        </div>
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-top: 1rem;">
            <div class="stat-card success">
                <div class="stat-icon">
                    <i data-lucide="calendar" style="width:24px; height:24px; color: #F15A22;"></i>
                </div>
                <div class="stat-label">Today Pay-in</div>
                <div class="stat-value">₹{{ number_format($todayPayin ?? 0, 2) }}</div>
                <div class="stat-change">{{ number_format($todayPayin ?? 0, 0) }}</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">
                    <i data-lucide="calendar-clock" style="width:24px; height:24px; color: #F15A22;"></i>
                </div>
                <div class="stat-label">Yesterday Pay-in</div>
                <div class="stat-value">₹{{ number_format($yesterdayPayin ?? 0, 2) }}</div>
                <div class="stat-change">{{ number_format($yesterdayPayin ?? 0, 0) }}</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">
                    <i data-lucide="calendar-days" style="width:24px; height:24px; color: #F15A22;"></i>
                </div>
                <div class="stat-label">Weekly Pay-in (7 Days)</div>
                <div class="stat-value">₹{{ number_format($weeklyPayin ?? 0, 2) }}</div>
                <div class="stat-change">{{ number_format($weeklyPayin ?? 0, 0) }}</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">
                    <i data-lucide="calendar-range" style="width:24px; height:24px; color: #F15A22;"></i>
                </div>
                <div class="stat-label">Monthly Pay-in</div>
                <div class="stat-value">₹{{ number_format($monthlyPayin ?? 0, 2) }}</div>
                <div class="stat-change">{{ number_format($monthlyPayin ?? 0, 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-3">
        <!-- Last 7 Days Trend -->
        <div class="col-md-8">
            <div class="chart-section">
                <div class="chart-header">
                    <h6 class="chart-title">📈 Last 7 Days Transaction Trend</h6>
                </div>
                <div class="chart-container">
                    <canvas id="weeklyTrendChart"></canvas>
                </div>
                        </div>
                    </div>

        <!-- Transaction Distribution -->
        <div class="col-md-4">
            <div class="chart-section">
                <div class="chart-header">
                    <h6 class="chart-title">📊 Transaction Distribution</h6>
                </div>
                <div class="chart-container">
                    <canvas id="distributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Monthly Volume Chart -->
    <div class="chart-section">
        <div class="chart-header">
            <h6 class="chart-title">💹 Monthly Transaction Volume (Last 6 Months)</h6>
        </div>
        <div class="chart-container">
            <canvas id="monthlyVolumeChart"></canvas>
        </div>
                </div>

    <!-- Recent Transactions Table -->
    <div class="transactions-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Recent Transactions</h5>
            <a href="/user/payin_report" class="btn btn-sm btn-primary">View All</a>
                    </div>
        
                    <div class="table-responsive">
            <table class="table table-modern">
                            <thead>
                                <tr>
                        <th>Transaction ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                    @forelse($recentTransactions as $txn)
                    <tr>
                        <td><strong>{{ $txn->transaction_id }}</strong></td>
                        <td>
                            <span class="badge {{ $txn->type == 'PayIn' ? 'badge-success' : 'badge-warning' }}">
                                {{ $txn->type }}
                            </span>
                        </td>
                        <td><strong>₹{{ number_format($txn->amount, 2) }}</strong></td>
                        <td>
                            @if($txn->status == 1)
                                <span class="badge badge-success">Success</span>
                            @elseif($txn->status == 2)
                                <span class="badge badge-danger">Failed</span>
                                        @else
                                <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                        <td>{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                                @empty
                                <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i data-lucide="inbox" style="width:48px; height:48px; opacity:0.3;"></i>
                            <p class="mt-2 mb-0">No transactions found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

</div>
@endsection

@section('js')
<script>
// Chart data from Laravel
const last7DaysData = @json($last7DaysData);
const monthlyData = @json($monthlyVolumeData);
const monthLabels = @json($months);

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Trend Chart (Line)
    const weeklyCtx = document.getElementById('weeklyTrendChart');
    if (weeklyCtx) {
        new Chart(weeklyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: last7DaysData.dates,
                datasets: [
                    {
                        label: 'Pay-in Success',
                        data: last7DaysData.payinSuccess,
                        borderColor: '#F15A22',
                        backgroundColor: 'rgba(241, 90, 34, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Pay-out Success',
                        data: last7DaysData.payoutSuccess,
                        borderColor: '#FCA5A5',
                        backgroundColor: 'rgba(252, 165, 165, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#1F2937'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#1F2937',
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(241, 90, 34, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#1F2937'
                        },
                        grid: {
                            color: 'rgba(241, 90, 34, 0.1)'
                        }
                    }
                }
            }
        });
    }

    // Distribution Chart (Doughnut)
    const distCtx = document.getElementById('distributionChart');
    if (distCtx) {
        const totalSuccess = {{ $tsuccesscount ?? 0 }};
        const totalPending = {{ $tpendingcount ?? 0 }};
        const totalFailed = {{ $tfailedcount ?? 0 }};
        
        new Chart(distCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Success', 'Pending', 'Failed'],
                datasets: [{
                    data: [totalSuccess, totalPending, totalFailed],
                    backgroundColor: [
                        '#F15A22',
                        '#FCA5A5',
                        '#991B1B'
                    ],
                    borderColor: '#1F1F1F',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#1F2937'
                        }
                    }
                }
            }
        });
    }

    // Monthly Volume Chart (Bar)
    const monthlyCtx = document.getElementById('monthlyVolumeChart');
    if (monthlyCtx) {
        const payinData = monthlyData.map(item => item.payin);
        const payoutData = monthlyData.map(item => item.payout);
        
        new Chart(monthlyCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: 'Pay-in',
                        data: payinData,
                        backgroundColor: '#F15A22',
                        borderRadius: 6,
                        borderColor: '#991B1B',
                        borderWidth: 1
                    },
                    {
                        label: 'Pay-out',
                        data: payoutData,
                        backgroundColor: '#FCA5A5',
                        borderRadius: 6,
                        borderColor: '#F15A22',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#1F2937'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#1F2937',
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(241, 90, 34, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#1F2937'
                        },
                        grid: {
                            color: 'rgba(241, 90, 34, 0.1)'
                        }
                    }
                }
            }
        });
    }

    // API Credentials Functions - Define globally
    window.toggleTokenVisibility = function() {
        const tokenInput = document.getElementById('token');
        const eyeIcon = document.getElementById('eye-icon');
        
        console.log('Toggle called', tokenInput, eyeIcon); // Debug
        
        if (tokenInput && eyeIcon) {
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
                console.log('Showing token'); // Debug
            } else {
                tokenInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
                console.log('Hiding token'); // Debug
            }
            
            // Re-render icons if lucide is available
            setTimeout(function() {
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                    console.log('Icons recreated'); // Debug
                }
            }, 10);
        }
    }

    window.copyToClipboard = function(fieldId, value) {
        // Create temporary input
        const tempInput = document.createElement('input');
        tempInput.value = value;
        document.body.appendChild(tempInput);
        
        // Select and copy
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            
            // Show success message using SweetAlert if available
            if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
                    text: fieldId === 'userid' ? 'User ID copied to clipboard' : 'API Token copied to clipboard',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast'
                    }
                });
            } else {
                alert(fieldId === 'userid' ? 'User ID copied!' : 'API Token copied!');
            }
        } catch (err) {
            console.error('Failed to copy:', err);
            alert('Failed to copy. Please select and copy manually.');
        }
        
        // Remove temporary input
        document.body.removeChild(tempInput);
    }
    });
</script>
@endsection
