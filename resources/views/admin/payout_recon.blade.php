@extends('admin.layout.user')
@section('title', 'Payout Reconciliation')
@section('content')

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">🔄 Payout Auto-Reconciliation</h4>
            <p class="text-muted">Automatic reconciliation runs every 5 minutes</p>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <button type="button" class="btn btn-primary btn-icon-text me-2 mb-2 mb-md-0" onclick="triggerManualRecon()">
                <i class="btn-icon-prepend" data-feather="refresh-cw"></i>
                Run Manual Reconciliation
            </button>
            <button type="button" class="btn btn-outline-info btn-icon-text me-2 mb-2 mb-md-0" onclick="refreshStats()">
                <i class="btn-icon-prepend" data-feather="download"></i>
                Refresh Stats
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Pending</h6>
                                <div class="dropdown mb-2">
                                    <i class="text-muted" data-feather="clock"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    <h3 class="mb-2" id="stat-total">{{ $stats['total_pending'] }}</h3>
                                    <div class="d-flex align-items-baseline">
                                        <p class="text-muted">All pending payouts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Ready to Reconcile</h6>
                                <div class="dropdown mb-2">
                                    <i class="text-success" data-feather="check-circle"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    <h3 class="mb-2 text-success" id="stat-2min">{{ $stats['older_than_2min'] }}</h3>
                                    <div class="d-flex align-items-baseline">
                                        <p class="text-muted">Older than 2 mins</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Moderate Delay</h6>
                                <div class="dropdown mb-2">
                                    <i class="text-warning" data-feather="alert-circle"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    <h3 class="mb-2 text-warning" id="stat-10min">{{ $stats['older_than_10min'] }}</h3>
                                    <div class="d-flex align-items-baseline">
                                        <p class="text-muted">Older than 10 mins</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Critical Delay</h6>
                                <div class="dropdown mb-2">
                                    <i class="text-danger" data-feather="alert-triangle"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    <h3 class="mb-2 text-danger" id="stat-1hour">{{ $stats['older_than_1hour'] }}</h3>
                                    <div class="d-flex align-items-baseline">
                                        <p class="text-muted">Older than 1 hour</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Gateway Breakdown -->
    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Pending Payouts by Gateway</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Gateway</th>
                                    <th>Pending Count</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="gateway-breakdown">
                                @foreach($stats['by_gateway'] as $gateway)
                                <tr>
                                    <td>{{ $gateway->gateway }}</td>
                                    <td><span class="badge bg-warning">{{ $gateway->count }}</span></td>
                                    <td>
                                        @if($gateway->count > 0)
                                            <span class="badge bg-info">Auto-Reconciling</span>
                                        @else
                                            <span class="badge bg-success">Clear</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gateway->count > 0)
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="bulkCancelByGateway('{{ $gateway->gateway_id }}', '{{ $gateway->gateway }}', {{ $gateway->count }})">
                                            <i data-feather="x-circle"></i> Cancel All
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @if(count($stats['by_gateway']) == 0)
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No pending payouts</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reconciliation Logs -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Recent Reconciliation Runs</h6>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Processed</th>
                                    <th>Success</th>
                                    <th>Failed</th>
                                </tr>
                            </thead>
                            <tbody id="recent-logs">
                                @foreach($recentLogs as $log)
                                <tr>
                                    <td>{{ $log->timestamp ? $log->timestamp->diffForHumans() : 'N/A' }}</td>
                                    <td><span class="badge bg-primary">{{ $log->data->processed ?? 0 }}</span></td>
                                    <td><span class="badge bg-success">{{ $log->data->success ?? 0 }}</span></td>
                                    <td><span class="badge bg-danger">{{ $log->data->failed ?? 0 }}</span></td>
                                </tr>
                                @endforeach
                                @if(count($recentLogs) == 0)
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent reconciliation runs</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Reconciliation Results -->
    <div class="row" id="manual-results-section" style="display: none;">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">Manual Reconciliation Results</h6>
                        <button type="button" class="btn-close" onclick="closeManualResults()"></button>
                    </div>
                    <div id="manual-results-content">
                        <!-- Results will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .badge {
        font-size: 0.875rem;
        padding: 0.35em 0.65em;
    }
    
    .table-sm td {
        padding: 0.5rem;
    }
    
    .btn-icon-prepend {
        margin-right: 8px;
    }
</style>

<script>
    // Trigger manual reconciliation
    function triggerManualRecon() {
        if (!confirm('Start manual reconciliation of pending payouts?')) {
            return;
        }

        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing...';

        fetch('/admin/payout_recon/trigger', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                limit: 100
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.status) {
                showManualResults(data);
                refreshStats();
                alert('✅ Reconciliation completed successfully!\n\n' +
                      'Duration: ' + data.duration + '\n' +
                      'Processed: ' + data.results.processed + '\n' +
                      'Success: ' + data.results.success + '\n' +
                      'Failed: ' + data.results.failed);
            } else {
                alert('❌ Reconciliation failed: ' + data.message);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('❌ Error: ' + error.message);
        });
    }

    // Refresh statistics
    function refreshStats() {
        fetch('/admin/payout_recon/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.total_pending;
            document.getElementById('stat-2min').textContent = data.older_than_2min;
            document.getElementById('stat-10min').textContent = data.older_than_10min;
            document.getElementById('stat-1hour').textContent = data.older_than_1hour;

            // Update gateway breakdown
            const gatewayTable = document.getElementById('gateway-breakdown');
            gatewayTable.innerHTML = '';
            
            if (data.by_gateway.length === 0) {
                gatewayTable.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No pending payouts</td></tr>';
            } else {
                data.by_gateway.forEach(gateway => {
                    const row = `
                        <tr>
                            <td>${gateway.gateway}</td>
                            <td><span class="badge bg-warning">${gateway.count}</span></td>
                            <td>
                                ${gateway.count > 0 
                                    ? '<span class="badge bg-info">Auto-Reconciling</span>' 
                                    : '<span class="badge bg-success">Clear</span>'}
                            </td>
                        </tr>
                    `;
                    gatewayTable.innerHTML += row;
                });
            }

            feather.replace();
        })
        .catch(error => {
            console.error('Error refreshing stats:', error);
        });
    }

    // Show manual reconciliation results
    function showManualResults(data) {
        const section = document.getElementById('manual-results-section');
        const content = document.getElementById('manual-results-content');
        
        let html = `
            <div class="alert alert-success">
                <strong>✅ Reconciliation Completed</strong><br>
                Duration: ${data.duration}<br>
                Total Processed: ${data.results.processed}<br>
                Success: ${data.results.success} | Failed: ${data.results.failed} | Skipped: ${data.results.skipped}
            </div>
        `;

        if (data.results.details && data.results.details.length > 0) {
            html += `
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.results.details.forEach(detail => {
                const statusClass = detail.status === 'success' ? 'success' : 
                                   detail.status === 'failed' ? 'danger' : 'secondary';
                html += `
                    <tr>
                        <td>${detail.txn_id}</td>
                        <td>${detail.gateway}</td>
                        <td><span class="badge bg-${statusClass}">${detail.status}</span></td>
                        <td>${detail.message}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        content.innerHTML = html;
        section.style.display = 'block';
    }

    // Close manual results
    function closeManualResults() {
        document.getElementById('manual-results-section').style.display = 'none';
    }

    // Auto-refresh stats every 30 seconds
    setInterval(refreshStats, 30000);

    // Bulk cancel by gateway
    function bulkCancelByGateway(gatewayId, gatewayName, count) {
        const minAge = prompt(`Cancel all ${gatewayName} transactions older than (hours):`, '1');
        if (minAge === null) return;

        const reason = prompt('Reason for cancellation:', 'Test transaction - Cancelled by admin');
        if (reason === null) return;

        if (!confirm(`⚠️ This will cancel ${count} pending ${gatewayName} transactions older than ${minAge} hour(s) and refund them.\n\nAre you sure?`)) {
            return;
        }

        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cancelling...';

        fetch('/admin/payout_recon/bulk-cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                gateway: gatewayId,
                min_age_hours: parseFloat(minAge),
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.status) {
                alert(`✅ Success!\n\nCancelled: ${data.cancelled} transactions\nRefunded: ₹${data.refunded}\n\n${data.message}`);
                refreshStats();
                location.reload(); // Reload to show updated stats
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('❌ Network error: ' + error.message);
        });
    }

    // Cancel all old transactions (any gateway)
    function cancelAllOldTransactions() {
        const minAge = prompt('Cancel ALL pending transactions older than (hours):', '24');
        if (minAge === null) return;

        const reason = prompt('Reason for cancellation:', 'Old test transaction - Bulk cancelled');
        if (reason === null) return;

        if (!confirm(`⚠️ DANGER: This will cancel ALL pending transactions from ALL gateways older than ${minAge} hour(s).\n\nAre you absolutely sure?`)) {
            return;
        }

        if (!confirm('⚠️⚠️ FINAL CONFIRMATION ⚠️⚠️\n\nThis action cannot be undone!\n\nCancel ALL old transactions?')) {
            return;
        }

        fetch('/admin/payout_recon/bulk-cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                gateway: 'all',
                min_age_hours: parseFloat(minAge),
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                alert(`✅ Success!\n\nCancelled: ${data.cancelled} transactions\nRefunded: ₹${data.refunded}`);
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Network error: ' + error.message);
        });
    }
</script>

@endsection

