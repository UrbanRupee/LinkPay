@extends('user.layout.NewUser')

@section('css')
    <style>
        /* Modern Orange, Cream & White Theme - Edit Profile Page */
        .profile-page-content {
            background: var(--cream);
            padding: 2rem;
            min-height: calc(100vh - 80px);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(241, 90, 34, 0.3);
        }

        .profile-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .profile-header h3 i {
            font-size: 2rem;
        }

        .profile-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .profile-card {
            background: var(--white);
            border: 2px solid rgba(241, 90, 34, 0.2);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(241, 90, 34, 0.15);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .profile-card:hover {
            box-shadow: 0 6px 25px rgba(241, 90, 34, 0.25);
            transform: translateY(-2px);
        }

        .profile-card .card-body {
            padding: 2rem;
        }

        .section-title {
            color: var(--primary-orange);
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary-orange);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            background-color: var(--white) !important;
            border: 2px solid rgba(241, 90, 34, 0.3) !important;
            color: var(--text-dark) !important;
            border-radius: 8px !important;
            padding: 0.75rem 1rem !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: var(--white) !important;
            border-color: var(--primary-orange) !important;
            color: var(--text-dark) !important;
            box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25) !important;
            transform: translateY(-1px);
        }

        .form-control:disabled {
            background-color: #F9FAFB !important;
            color: var(--text-light) !important;
            border-color: rgba(241, 90, 34, 0.15) !important;
            cursor: not-allowed;
        }


        .btn-primary {
            background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange)) !important;
            border: none !important;
            padding: 0.875rem 2.5rem !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 15px rgba(241, 90, 34, 0.4) !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 25px rgba(241, 90, 34, 0.5) !important;
        }

        .btn-primary i {
            font-size: 1.1rem;
        }

        .text-danger {
            color: var(--primary-orange) !important;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .info-badge {
            display: inline-block;
            background: rgba(241, 90, 34, 0.1);
            color: var(--primary-orange);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.5rem;
            border-left: 3px solid var(--primary-orange);
        }

        @media (max-width: 768px) {
            .profile-page-content {
                padding: 1rem;
            }

            .profile-card .card-body {
                padding: 1.5rem;
            }

            .profile-header {
                padding: 1.5rem;
            }

            .profile-header h3 {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection

@section('content')
<div class="profile-page-content">
    <div class="container-fluid">
        <!-- Profile Header -->
        <div class="profile-header">
            <h3>Edit Profile</h3>
            <p>Update your personal and banking information</p>
        </div>

        <form action="#" id="edit_profile" class="form-horizontal">
            @csrf
            
            <!-- Demographic Details Section -->
            <div class="profile-card">
                <div class="card-body">
                    <div class="section-title">
                        Demographic Details
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="partner_id">
                                    Partner ID
                                </label>
                                <input class="form-control" type="text" id="partner_id"
                                    value="{{ user('userid') }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="partner_name">
                                    Partner Name
                                </label>
                                <input class="form-control" type="text" id="partner_name"
                                    value="{{ user('name') }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="joining_date">
                                    Joining Date
                                </label>
                                <input class="form-control" type="text" id="joining_date"
                                    value="{{ dformat(user('created_at'), 'd-m-Y') }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="email_id">
                                    Email ID
                                </label>
                                <input class="form-control" type="text" id="email_id"
                                    value="{{strtoupper(user('email'))}}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="mobile">
                                    Mobile Number
                                </label>
                                <input class="form-control" type="text" id="mobile" value="{{user('mobile')}}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="mobile_2">
                                    Secondary Mobile Number
                                </label>
                                <input class="form-control" type="text" name="mobile_2" id="mobile_2" value="{{user('mobile_2')}}" placeholder="Enter secondary mobile number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="dob">
                                    Date Of Birth
                                </label>
                                <input class="form-control" name="dob" type="text" id="dob"
                                    value="{{ user('dob') }}" placeholder="DD-MM-YYYY">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="aadhar_no">
                                    Aadhar No
                                </label>
                                <input class="form-control" name="aadhar_no" type="text" id="aadhar_no"
                                    value="{{ user('aadhar_card') }}" placeholder="Enter Aadhar number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="pan_no">
                                    PAN No
                                </label>
                                <input class="form-control" name="pan_no" type="text" id="pan_no"
                                    value="{{ user('pan_card') }}" placeholder="Enter PAN number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="company_name">
                                    Company Name
                                </label>
                                <input class="form-control" name="company_name" type="text" id="company_name"
                                    value="{{ user('company_name') }}" placeholder="Enter company name">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Details Section -->
            <div class="profile-card">
                <div class="card-body">
                    <div class="section-title">
                        Address Details
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="address_1">
                                    Address Line 1
                                </label>
                                <textarea class="form-control" name="address_1" id="address_1" rows="3" placeholder="Enter your address">{{ user('address') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="address_2">
                                    Address Line 2
                                </label>
                                <textarea class="form-control" name="address_2" id="address_2" rows="3" placeholder="Enter additional address details">{{ user('address_1') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="state">
                                    State
                                </label>
                                <input class="form-control" name="state" type="text" id="state" value="{{ user('state') }}" placeholder="Enter state">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="pincode">
                                    Pincode
                                </label>
                                <input class="form-control" name="pincode" type="number" id="pincode" value="{{ user('pincode') }}" placeholder="Enter pincode">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details Section -->
            <div class="profile-card">
                <div class="card-body">
                    <div class="section-title text-danger">
                        Bank Details
                    </div>
                    <div class="info-badge">
                        Please ensure your bank details are accurate for seamless transactions
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="bank_name">
                                    Bank Name
                                </label>
                                <input class="form-control" name="bank_name" type="text" id="bank_name" value="{{bank(user('userid'),'bank_name')}}" placeholder="Enter bank name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="bankholdername">
                                    Account Holder Name
                                </label>
                                <input class="form-control" type="text" id="bankholdername" value="{{strtoupper(user('name'))}}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="account_no">
                                    Bank Account Number
                                </label>
                                <input class="form-control" type="text" id="account_no" value="{{bank(user('userid'),'account_no')}}" name="account_no" placeholder="Enter account number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="ifsc_code">
                                    IFSC Code
                                </label>
                                <input class="form-control" type="text" id="ifsc_code" value="{{bank(user('userid'),'ifsc_code')}}" name="ifsc_code" placeholder="Enter IFSC code">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    Update Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
    <script>
        formasync('edit_profile');
        $("#edit_profile").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/api/edit_profile') }}", new FormData(form), form,
                    "javascript:;",
                    "#");
            }
        });
    </script>
@endsection
