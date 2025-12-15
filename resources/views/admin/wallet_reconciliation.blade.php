@extends('admin.layout.user')
@section('title', 'Wallet Reconciliation')
@section('content')

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">💰 PayIn Wallet Reconciliation</h4>
            <p class="text-muted">Monitor and auto-fix wallet balance discrepancies</p>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <button type="button" class="btn btn-warning btn-icon-text me-2 mb-2 mb-md-0" onclick="bulkFixWallets()">
                <i class="btn-icon-prepend" data-feather="tool"></i>
                Bulk Fix All Issues
            </button>
            {{-- REMOVED: NSO Issues button - Only Easebuzz now --}}
            <button type="button" class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0" onclick="location.reload()">
                <i class="btn-icon-prepend" data-feather="refresh-cw"></i>
                Refresh
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-0">Total Users</h6>
                    <h3 class="mb-2">{{ count($results) }}</h3>
                    <p class="text-muted mb-0">Active users monitored</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-0 text-danger">Users with Issues</h6>
                    <h3 class="mb-2 text-danger">{{ count($issues) }}</h3>
                    <p class="text-muted mb-0">Require attention</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-0 text-success">Balanced Users</h6>
                    <h3 class="mb-2 text-success">{{ count($results) - count($issues) }}</h3>
                    <p class="text-muted mb-0">No discrepancies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-0 text-warning">Easebuzz Pending</h6>
                    <h3 class="mb-2 text-warning" id="easebuzz-pending-count">
                        {{ collect($results)->sum('pending_easebuzz') }}
                    </h3>
                    <p class="text-muted mb-0">Pending transactions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Issues Table -->
    @if(count($issues) > 0)
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-danger">⚠️ Users with Balance Issues</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Gateway</th>
                                    <th>Current PayIn</th>
                                    <th>Expected PayIn</th>
                                    <th>Difference</th>
                                    <th>Settled</th>
                                    <th>Pending</th>
                                    <th>Missing Logs</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($issues as $issue)
                                <tr>
                                    <td><strong>{{ $issue['userid'] }}</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ gateway_name($issue['gateway'], 'payin') }}</span>
                                    </td>
                                    <td>₹{{ number_format($issue['current_payin'], 2) }}</td>
                                    <td>₹{{ number_format($issue['expected_payin'], 2) }}</td>
                                    <td class="{{ $issue['difference'] > 0 ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ $issue['difference'] > 0 ? '+' : '' }}₹{{ number_format($issue['difference'], 2) }}</strong>
                                    </td>
                                    <td>₹{{ number_format($issue['settled_amount'], 2) }}</td>
                                    <td>
                                        @if($issue['pending_count'] > 0)
                                        <span class="badge bg-warning">{{ $issue['pending_count'] }}</span>
                                        @else
                                        <span class="badge bg-success">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($issue['missing_logs'] > 0)
                                        <span class="badge bg-danger" title="Payments successful but not logged to transactions table">{{ $issue['missing_logs'] }}</span>
                                        @else
                                        <span class="badge bg-success">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewDiagnostic('{{ $issue['userid'] }}')" title="View detailed breakdown">
                                            <i data-feather="info"></i> Details
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="fixWallet('{{ $issue['userid'] }}')">
                                            <i data-feather="check"></i> Fix
                                        </button>
                                        @if($issue['gateway'] == '28' && $issue['pending_count'] > 0)
                                        <button class="btn btn-sm btn-warning" onclick="checkEasebuzzPending('{{ $issue['userid'] }}')">
                                            <i data-feather="refresh-cw"></i> Check Easebuzz
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- All Users Table -->
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">📊 All Users Wallet Status</h6>
                    <div class="table-responsive">
                        <table class="table table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Gateway</th>
                                    <th>Current</th>
                                    <th>Calculated</th>
                                    <th>Settled</th>
                                    <th>Expected</th>
                                    <th>Difference</th>
                                    <th>Pending</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr class="{{ $result['has_issue'] ? 'table-warning' : '' }}">
                                    <td><strong>{{ $result['userid'] }}</strong></td>
                                    <td><small>{{ gateway_name($result['gateway'], 'payin') }}</small></td>
                                    <td>₹{{ number_format($result['current_payin'], 2) }}</td>
                                    <td>₹{{ number_format($result['calculated_payin'], 2) }}</td>
                                    <td>₹{{ number_format($result['settled_amount'], 2) }}</td>
                                    <td>₹{{ number_format($result['expected_payin'], 2) }}</td>
                                    <td class="{{ $result['difference'] > 0 ? 'text-success' : ($result['difference'] < 0 ? 'text-danger' : 'text-muted') }}">
                                        {{ $result['difference'] > 0 ? '+' : '' }}₹{{ number_format($result['difference'], 2) }}
                                    </td>
                                    <td>
                                        @if($result['pending_count'] > 0)
                                        <span class="badge bg-warning">{{ $result['pending_count'] }}</span>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if(abs($result['difference']) < 1)
                                        <span class="badge bg-success">✓ OK</span>
                                        @elseif($result['missing_logs'] > 50)
                                        <span class="badge bg-info" title="Wallet correct, transactions table incomplete">ℹ️ Log Issue</span>
                                        @else
                                        <span class="badge bg-danger">⚠ Issue</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fix single wallet
    function fixWallet(userid) {
        if (!confirm(`Fix wallet for ${userid}?`)) return;

        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`/admin/wallet/fix/${userid}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.status) {
                alert(`✅ Wallet Fixed!\n\nOld: ₹${data.old_balance}\nNew: ₹${data.new_balance}\nDifference: ₹${data.difference}`);
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('❌ Error: ' + error.message);
        });
    }

    // Bulk fix all wallets
    function bulkFixWallets() {
        if (!confirm('⚠️ Fix ALL wallets with issues?\n\nThis will update wallet balances for all users with discrepancies.')) return;

        fetch('/admin/wallet/bulk-fix', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ min_difference: 1 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                alert(`✅ Bulk Fix Complete!\n\nFixed: ${data.fixed}\nSkipped: ${data.skipped}\nErrors: ${data.errors.length}`);
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        });
    }

    // View detailed diagnostic breakdown
    function viewDiagnostic(userid) {
        fetch(`/admin/wallet/diagnostic/${userid}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                const breakdown = data.breakdown;
                const calc = data.calculation;
                
                let message = `📊 Detailed Breakdown for ${userid}\n\n`;
                message += `💰 Successful Payments:\n`;
                message += `   Total Amount: ₹${parseFloat(breakdown.successful_payments.total_amount).toFixed(2)}\n`;
                message += `   Total Tax: ₹${parseFloat(breakdown.successful_payments.total_tax).toFixed(2)}\n`;
                message += `   Net Amount: ₹${parseFloat(breakdown.successful_payments.total_net).toFixed(2)}\n`;
                message += `   Count: ${breakdown.successful_payments.count}\n\n`;
                
                message += `💸 Settlements:\n`;
                message += `   Total Deducted: ₹${parseFloat(breakdown.settlements.total).toFixed(2)}\n`;
                message += `   Count: ${breakdown.settlements.count}\n\n`;
                
                if (breakdown.admin_deductions.total > 0) {
                    message += `⚠️ Admin Deductions:\n`;
                    message += `   Total Deducted: ₹${parseFloat(breakdown.admin_deductions.total).toFixed(2)}\n`;
                    message += `   Count: ${breakdown.admin_deductions.count}\n\n`;
                }
                
                if (breakdown.other_debits.count > 0) {
                    message += `🔴 Other Debits:\n`;
                    message += `   Count: ${breakdown.other_debits.count}\n\n`;
                }
                
                message += `📐 Calculation:\n`;
                message += `   Calculated PayIn: ₹${parseFloat(calc.calculated_payin).toFixed(2)}\n`;
                message += `   - Settlements: ₹${parseFloat(calc.minus_settlements).toFixed(2)}\n`;
                message += `   - Other Debits: ₹${parseFloat(calc.minus_other_debits).toFixed(2)}\n`;
                message += `   = Expected PayIn: ₹${parseFloat(calc.equals_expected_payin).toFixed(2)}\n\n`;
                message += `💵 Current PayIn: ₹${parseFloat(calc.current_payin).toFixed(2)}\n`;
                message += `📊 Difference: ₹${parseFloat(calc.difference).toFixed(2)}`;
                
                alert(message);
            } else {
                alert('❌ Error: ' + (data.message || 'Failed to fetch diagnostic'));
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        });
    }

    // ✅ Check Easebuzz pending transactions (ONLY EASEBUZZ NOW)
    function checkEasebuzzPending(userid) {
        if (!confirm(`Check Easebuzz pending transactions for ${userid}?`)) return;

        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`/admin/wallet/check-pending/${userid}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.status) {
                alert(`✅ Easebuzz Check Complete!\n\nGateway: ${data.gateway}\nChecked: ${data.checked}\nUpdated: ${data.updated}\nFailed: ${data.failed}`);
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('❌ Error: ' + error.message);
        });
    }

    // REMOVED: NSO monitoring functions - Only Easebuzz now

    // Initialize Feather icons
    feather.replace();
</script>

@endsection

