@extends('admin.layout.user')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css">
<style>
  .page-breadcrumb { margin-bottom: .75rem; }

  /* --- Compact rectangular tiles --- */
  .provider-tile {
    border: 1px solid #e9ecef;
    border-radius: .5rem;
    padding: .7rem .9rem;
    background: #fff;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: .75rem;
    margin-bottom: .5rem;
  }
  .provider-tile:hover {
    transform: translateY(-1px);
    box-shadow: 0 .2rem .6rem rgba(0,0,0,.06);
    border-color: #dee2e6;
  }
  .provider-left { display: flex; align-items: center; gap: .6rem; min-width: 0; }
  .status-dot { width: .55rem; height: .55rem; border-radius: 50%; flex-shrink: 0; }
  .dot-active { background: #28a745; }
  .dot-inactive { background: #dc3545; }
  .dot-hold { background: #ffc107; }

  .provider-name {
    font-weight: 600;
    color: #0d6efd;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 280px;
  }
  .provider-meta {
    font-size: .78rem; color: #6c757d;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 300px;
  }
  .provider-actions { display: flex; align-items: center; gap: .35rem; }

  /* --- Inline details box --- */
  .details-wrap {
    border: 1px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 .5rem .5rem;
    background: #fcfcfd;
    margin-top: -0.45rem;
    margin-bottom: .6rem;
    padding: .9rem;
  }
  .details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: .6rem .9rem;
  }
  .details-grid .label { font-size: .78rem; color: #6c757d; }
  .details-grid .value { font-size: .9rem; }
  .section-title {
    font-size: .85rem; color: #6c757d; text-transform: uppercase; letter-spacing: .02em;
    margin: .6rem 0 .25rem;
  }
  .text-wrap-break { word-break: break-all; white-space: normal; }

  /* --- Modal: slide (no-scroll) form --- */
  .modal-xl { max-width: 920px; }
  .modal-body {
    padding-top: .75rem;
  }
  .form-steps-head {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem;
    margin-bottom: .75rem;
  }
  .step-pill {
    border: 1px solid #e9ecef; border-radius: 999px; padding: .45rem .6rem;
    display: flex; align-items: center; gap: .5rem; justify-content: center;
    font-size: .85rem; color: #6c757d; background: #fff;
  }
  .step-pill.active { border-color: #0d6efd; color: #0d6efd; background: #f0f6ff; }
  .carousel-inner { border: 1px solid #eef1f5; border-radius: .5rem; background: #fff; }
  .slide-pane {
    padding: .9rem .9rem 0 .9rem;
    min-height: 340px; /* keep single-screen height */
    display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .9rem;
  }
  .slide-pane h6 {
    grid-column: 1 / -1; color: #6c757d; text-transform: uppercase; font-size: .85rem; margin: .25rem 0 .1rem;
  }
  .slide-pane .full { grid-column: 1 / -1; }
  .form-text-sm { font-size: .8rem; color: #6c757d; }
  .modal-footer {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem .75rem;
  }
  .nav-btns { display: flex; gap: .5rem; }

  @media (max-width: 576px) {
    .provider-name { max-width: 150px; }
    .provider-meta { display: none; }
    .details-grid { grid-template-columns: 1fr; }
    .slide-pane { grid-template-columns: 1fr; min-height: 420px; } /* mobile fallback */
  }
</style>
@endsection

@section('content')
<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
      <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
    </ol>
  </nav>

  <div class="row mb-3">
    <div class="col-12 text-end">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#providerModal" onclick="clearForm()">
        <i data-feather="plus" class="me-2"></i> Add New Provider
      </button>
    </div>
  </div>

  <div class="row">
    @if ($providers->count() > 0)
      <div class="col-12">
        @foreach ($providers as $item)
          {{-- Tile --}}
          <div class="provider-tile" id="provider-tile-{{ $item->id }}"
               onclick="toggleProviderDetails({{ $item->id }})"
               data-id="{{ $item->id }}" aria-controls="provider-details-{{ $item->id }}" aria-expanded="false">
            <div class="provider-left">
              <span class="status-dot {{ $item->status === 'active' ? 'dot-active' : ($item->status === 'inactive' ? 'dot-inactive' : 'dot-hold') }}"></span>
              <div>
                <div class="provider-name">{{ $item->name }}</div>
                <div class="provider-meta">
                  {{ $item->location ?? 'N/A' }} • {{ $item->service_type ?? 'Service' }}
                </div>
              </div>
            </div>
            <div class="provider-actions">
              @if ($item->status == 'active')
                <span class="badge bg-success">Active</span>
              @elseif($item->status == 'inactive')
                <span class="badge bg-danger">Inactive</span>
              @else
                <span class="badge bg-warning">Hold</span>
              @endif
              <button class="btn btn-sm btn-outline-info"
                      onclick="event.stopPropagation(); editProvider({{ $item->id }})">
                <i data-feather="edit"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger"
                      onclick="event.stopPropagation(); deleteProvider({{ $item->id }})">
                <i data-feather="trash-2"></i>
              </button>
            </div>
          </div>

          {{-- Collapsible inline details --}}
          <div id="provider-details-{{ $item->id }}" class="collapse" data-loaded="0">
            <div class="details-wrap">
              <div class="text-muted">Loading...</div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="col-12">
        <div class="alert alert-info text-center" role="alert">
          No {{ $title }} found. Click "Add New Provider" to get started!
        </div>
      </div>
    @endif
  </div>
</div>

<!-- Modal: Add/Edit Provider (Slides, no scrolling) -->
<div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="providerModalLabel">Add New Provider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="providerForm">
        @csrf
        <input type="hidden" id="provider_id" name="id">

        <div class="modal-body">
          <!-- Step pills -->
          <div class="form-steps-head" id="formStepsHead">
            <div class="step-pill active" data-step="0">1. Basic</div>
            <div class="step-pill" data-step="1">2. Payments</div>
            <div class="step-pill" data-step="2">3. Settlement</div>
            <div class="step-pill" data-step="3">4. Contact & Status</div>
          </div>

          <!-- Slides -->
          <div id="providerCarousel" class="carousel slide" data-bs-interval="false" data-bs-ride="false">
            <div class="carousel-inner">

              <!-- Slide 1: Basic -->
              <div class="carousel-item active">
                <div class="slide-pane">
                  <h6>Basic Information</h6>
                  <div class="full"></div>
                  <div>
                    <label for="name" class="form-label">Provider Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div class="invalid-feedback" id="name-error"></div>
                  </div>
                  <div>
                    <label for="service_type" class="form-label">Service Type</label>
                    <input type="text" class="form-control" id="service_type" name="service_type" placeholder="e.g., PG / Payout / APM Aggregator">
                  </div>
                  <div>
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="e.g., India, EU, Global">
                  </div>
                  <div>
                    <label for="url" class="form-label">URL</label>
                    <input type="url" class="form-control" id="url" name="url" placeholder="https://provider.com">
                    <div class="form-text-sm">Official site or dashboard link</div>
                  </div>
                </div>
              </div>

              <!-- Slide 2: Payments -->
              <div class="carousel-item">
                <div class="slide-pane">
                  <h6>Payment Methods</h6>
                  <div class="full"></div>
                  <div class="full">
                    <label for="commercial_mdr" class="form-label">Commercial (MDR details for PG)</label>
                    <input type="text" class="form-control" id="commercial_mdr" name="commercial_mdr" placeholder="e.g., 1.5% Credit, 0.5% Debit, 0% UPI">
                  </div>
                  <div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="cards" name="cards">
                      <label class="form-check-label" for="cards">Cards</label>
                    </div>
                  </div>
                  <div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="apms" name="apms">
                      <label class="form-check-label" for="apms">APMs</label>
                    </div>
                  </div>
                  <div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="bank_transfer" name="bank_transfer">
                      <label class="form-check-label" for="bank_transfer">Bank Transfer</label>
                    </div>
                  </div>

                  <h6 class="full">Transaction Direction</h6>
                  <div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="in" name="in">
                      <label class="form-check-label" for="in">In (Incoming)</label>
                    </div>
                  </div>
                  <div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="out" name="out">
                      <label class="form-check-label" for="out">Out (Outgoing)</label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Slide 3: Settlement -->
              <div class="carousel-item">
                <div class="slide-pane">
                  <h6>Settlement</h6>
                  <div class="full"></div>
                  <div>
                    <label for="settlement_timeline" class="form-label">Timeline</label>
                    <input type="text" class="form-control" id="settlement_timeline" name="settlement_timeline" placeholder="e.g., T+1, T+2, Instant">
                  </div>
                  <div>
                    <label for="settlement_mode" class="form-label">Mode</label>
                    <input type="text" class="form-control" id="settlement_mode" name="settlement_mode" placeholder="e.g., NEFT, RTGS, SWIFT, Wallet">
                  </div>
                  <div class="full">
                    <div class="form-text-sm">Specify any special rules or holds if applicable.</div>
                  </div>
                </div>
              </div>

              <!-- Slide 4: Contact & Status -->
              <div class="carousel-item">
                <div class="slide-pane">
                  <h6>Contact</h6>
                  <div class="full"></div>
                  <div>
                    <label for="contact_spoc" class="form-label">Contact SPOC</label>
                    <input type="text" class="form-control" id="contact_spoc" name="contact_spoc" placeholder="e.g., John Doe">
                  </div>
                  <div>
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="+91 98xxxxxxx">
                  </div>

                  <div class="full">
                    <label for="risk_and_blacklisting" class="form-label">Risk and Blacklisting Notes</label>
                    <textarea class="form-control" id="risk_and_blacklisting" name="risk_and_blacklisting" rows="3" placeholder="High-risk verticals, blacklisted MCCs, etc."></textarea>
                  </div>

                  <h6 class="full">Status</h6>
                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="status" id="status_active" value="active">
                      <label class="form-check-label" for="status_active">Active</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="status" id="status_inactive" value="inactive">
                      <label class="form-check-label" for="status_inactive">Inactive</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="status" id="status_hold" value="hold">
                      <label class="form-check-label" for="status_hold">Hold</label>
                    </div>
                  </div>
                </div>
              </div>

            </div> <!-- /carousel-inner -->
          </div> <!-- /carousel -->
        </div> <!-- /modal-body -->

        <div class="modal-footer">
          <div class="form-text-sm" id="stepHint">Step 1 of 4</div>
          <div class="nav-btns">
            <button type="button" class="btn btn-light" id="prevBtn" disabled>Back</button>
            <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            <button type="submit" class="btn btn-success d-none" id="saveProviderBtn">Save Provider</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>
  $(document).ready(function() { feather.replace(); });

  const providerModal = new bootstrap.Modal(document.getElementById('providerModal'));
  const providerForm = $('#providerForm');
  const providerIdField = $('#provider_id');
  const modalTitle = $('#providerModalLabel');
  const stepPills = $('#formStepsHead .step-pill');
  const carouselEl = document.getElementById('providerCarousel');
  const carousel = new bootstrap.Carousel(carouselEl, { interval: false, ride: false, wrap: false });
  const prevBtn = $('#prevBtn');
  const nextBtn = $('#nextBtn');
  const saveBtn = $('#saveProviderBtn');
  const stepHint = $('#stepHint');
  let currentStep = 0;
  const totalSteps = 4;

  function setStep(step) {
    currentStep = step;
    carousel.to(step);
    stepPills.removeClass('active');
    stepPills.eq(step).addClass('active');
    prevBtn.prop('disabled', step === 0);
    nextBtn.toggleClass('d-none', step === totalSteps - 1);
    saveBtn.toggleClass('d-none', step !== totalSteps - 1);
    stepHint.text(`Step ${step + 1} of ${totalSteps}`);
  }

  stepPills.on('click', function() {
    const step = +$(this).data('step');
    setStep(step);
  });

  prevBtn.on('click', function() {
    if (currentStep > 0) setStep(currentStep - 1);
  });

  nextBtn.on('click', function() {
    // Minimal required validation per step
    if (currentStep === 0) {
      if (!$('#name').val().trim()) {
        $('#name').addClass('is-invalid');
        $('#name-error').text('Provider name is required.');
        return;
      }
      $('#name').removeClass('is-invalid'); $('#name-error').text('');
    }
    setStep(currentStep + 1);
  });

  function clearForm() {
    providerForm[0].reset();
    providerIdField.val('');
    modalTitle.text('Add New Provider');
    saveBtn.text('Save Provider').removeClass('btn-warning').addClass('btn-success');
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    $('input[name="status"][value="active"]').prop('checked', true);
    setStep(0);
  }

  providerForm.on('submit', function(e) {
    e.preventDefault();
    const id = providerIdField.val();
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/admin/providers/${id}` : '/admin/providers';
    const formData = new FormData(this);
    formData.append('_method', method);

    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function(response) {
        if (response.status === 'success') {
          alert(response.message);
          providerModal.hide();
          location.reload();
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function(xhr) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          // Jump back to first error step if needed
          const errors = xhr.responseJSON.errors;
          const fieldOrder = ['name','service_type','location','url','commercial_mdr','settlement_timeline','settlement_mode','contact_spoc','contact_number','risk_and_blacklisting'];
          let firstErrorField = null;
          for (const key in errors) {
            if (!firstErrorField || fieldOrder.indexOf(key) < fieldOrder.indexOf(firstErrorField)) {
              firstErrorField = key;
            }
            $(`#${key}`).addClass('is-invalid');
            $(`#${key}-error`).text(errors[key][0]);
          }
          if (firstErrorField) {
            // map field to step
            const fieldStepMap = {
              name:0, service_type:0, location:0, url:0,
              commercial_mdr:1, cards:1, apms:1, bank_transfer:1, in:1, out:1,
              settlement_timeline:2, settlement_mode:2,
              contact_spoc:3, contact_number:3, risk_and_blacklisting:3, status:3
            };
            setStep(fieldStepMap[firstErrorField] ?? 0);
          }
        } else {
          alert('An error occurred: ' + (xhr.responseJSON?.message || 'Please try again.'));
        }
      }
    });
  });

  // Inline details under a tile
  function toggleProviderDetails(id) {
    const $panel = $(`#provider-details-${id}`);
    const loaded = $panel.attr('data-loaded') === '1';
    const isShown = $panel.hasClass('show');

    if (isShown) { $panel.collapse('hide'); return; }

    if (!loaded) {
      $panel.find('.details-wrap').html('<div class="text-muted">Loading...</div>');
      $.ajax({
        url: `/admin/providers/${id}/edit`,
        type: 'GET',
        success: function(data) {
          const statusBadge = data.status === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : (data.status === 'inactive' ? '<span class="badge bg-danger">Inactive</span>' : '<span class="badge bg-warning">Hold</span>');

          const methods = [
            data.cards ? '<span class="badge bg-info me-1">Cards</span>' : '',
            data.apms ? '<span class="badge bg-warning me-1">APMs</span>' : '',
            data.bank_transfer ? '<span class="badge bg-danger me-1">Bank Transfer</span>' : ''
          ].join(' ');

          const direction = [
            data.in ? '<span class="badge bg-primary me-1">Incoming (In)</span>' : '',
            data.out ? '<span class="badge bg-secondary me-1">Outgoing (Out)</span>' : ''
          ].join(' ');

          const urlHtml = data.url
            ? `<a href="${data.url}" target="_blank" class="text-primary text-wrap-break">${data.url}</a>`
            : 'N/A';

          const html = `
            <div class="details-inner">
              <div class="d-flex align-items-center gap-2 mb-2">
                <h6 class="mb-0">${data.name ?? ''}</h6>
                ${statusBadge}
              </div>

              <div class="details-grid">
                <div><div class="label">Location</div><div class="value">${data.location ?? 'N/A'}</div></div>
                <div><div class="label">Service</div><div class="value">${data.service_type ?? 'N/A'}</div></div>
                <div><div class="label">URL</div><div class="value">${urlHtml}</div></div>
                <div><div class="label">Status</div><div class="value">${data.status ?? 'N/A'}</div></div>
              </div>

              <div class="section-title">Payment Methods & MDR</div>
              <div class="mb-2">
                ${data.commercial_mdr ? `<div class="mb-1"><span class="badge bg-success">Commercial</span> <span class="text-wrap-break ms-1">${data.commercial_mdr}</span></div>` : ''}
                ${methods || '<span class="text-muted">No methods configured</span>'}
              </div>

              <div class="section-title">Transaction Direction</div>
              <div class="mb-2">${direction || '<span class="text-muted">Not set</span>'}</div>

              <div class="section-title">Settlement</div>
              <div class="details-grid">
                <div><div class="label">Timeline</div><div class="value">${data.settlement_timeline ?? 'N/A'}</div></div>
                <div><div class="label">Mode</div><div class="value">${data.settlement_mode ?? 'N/A'}</div></div>
              </div>

              <div class="section-title">Contact</div>
              <div class="details-grid">
                <div><div class="label">SPOC</div><div class="value">${data.contact_spoc ?? 'N/A'}</div></div>
                <div><div class="label">Number</div><div class="value">${data.contact_number ?? 'N/A'}</div></div>
              </div>

              ${data.risk_and_blacklisting ? `
                <div class="section-title">Risk Notes</div>
                <p class="small text-danger text-wrap-break mb-0">${data.risk_and_blacklisting}</p>
              ` : ''}

              <div class="mt-3 d-flex gap-2">
                <button class="btn btn-outline-info btn-sm" onclick="event.stopPropagation(); editProvider(${id})"><i data-feather="edit"></i> Edit</button>
                <button class="btn btn-outline-danger btn-sm" onclick="event.stopPropagation(); deleteProvider(${id})"><i data-feather="trash-2"></i> Delete</button>
              </div>
            </div>
          `;

          $panel.find('.details-wrap').html(html);
          $panel.attr('data-loaded', '1');
          feather.replace();
          $panel.collapse('show');
        },
        error: function() {
          $panel.find('.details-wrap').html('<div class="text-danger">Failed to load details. Please try again.</div>');
          $panel.collapse('show');
        }
      });
    } else {
      $panel.collapse('toggle');
    }
  }

  // Edit existing: prefill + jump to step 0
  function editProvider(id) {
    clearForm();
    $.ajax({
      url: `/admin/providers/${id}/edit`,
      type: 'GET',
      success: function(data) {
        $('#providerModalLabel').text('Edit Provider');
        $('#saveProviderBtn').text('Update Provider').removeClass('btn-success').addClass('btn-warning');
        providerIdField.val(data.id);

        // Fill slides
        $('#name').val(data.name);
        $('#service_type').val(data.service_type);
        $('#location').val(data.location);
        $('#url').val(data.url);

        $('#commercial_mdr').val(data.commercial_mdr);
        $('#cards').prop('checked', !!data.cards);
        $('#apms').prop('checked', !!data.apms);
        $('#bank_transfer').prop('checked', !!data.bank_transfer);
        $('#in').prop('checked', !!data.in);
        $('#out').prop('checked', !!data.out);

        $('#settlement_timeline').val(data.settlement_timeline);
        $('#settlement_mode').val(data.settlement_mode);

        $('#contact_spoc').val(data.contact_spoc);
        $('#contact_number').val(data.contact_number);
        $('#risk_and_blacklisting').val(data.risk_and_blacklisting);
        $(`input[name="status"][value="${data.status}"]`).prop('checked', true);

        setStep(0);
        providerModal.show();
      },
      error: function() { alert('Error fetching provider data.'); }
    });
  }

  function deleteProvider(id) {
    if (!confirm('Are you sure you want to delete this provider?')) return;
    $.ajax({
      url: `/admin/providers/${id}`,
      type: 'POST',
      data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
      success: function(response) {
        if (response.status === 'success') {
          alert(response.message);
          $(`#provider-tile-${id}`).remove();
          $(`#provider-details-${id}`).remove();
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function(xhr) {
        alert('An error occurred: ' + (xhr.responseJSON?.message || 'Please try again.'));
      }
    });
  }
</script>
@endsection
