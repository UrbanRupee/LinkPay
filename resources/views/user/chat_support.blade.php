@extends('user.layout.NewUser')

@section('css')
    <style>
        /* Re-using general styles for consistency */
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary-text-color);
        }

        /* Support Contact Cards */
        .support-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            display: flex;
            flex-direction: column; /* Stack content vertically */
            align-items: center; /* Center horizontally */
            text-align: center;
            margin-bottom: 1.5rem; /* Consistent spacing */
            transition: transform 0.2s ease-in-out;
            border: 1px solid rgba(0, 0, 0, 0.05);
            min-height: 200px; /* Ensure consistent height for visual balance */
        }
        .support-card:hover {
            transform: translateY(-3px);
        }

        .support-card .icon-wrapper {
            background-color: rgba(0, 123, 255, 0.1); /* Default info blue */
            border-radius: 50%;
            width: 60px; /* Standard icon wrapper size */
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-bottom: 1rem; /* Space below icon */
        }
        .support-card .icon-wrapper i {
            color: var(--primary-color);
            font-size: 2rem;
        }

        .support-card .contact-detail {
            font-size: 1.25rem; /* Slightly larger for main contact info */
            font-weight: 600;
            margin-bottom: 0.5rem; /* Space below the detail */
            line-height: 1.4;
        }
        /* Style for links within contact details */
        .support-card .contact-detail a {
            color: var(--primary-color); /* Use primary color for links */
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .support-card .contact-detail a:hover {
            color: #F15A22; /* Darker blue on hover */
            text-decoration: underline;
        }

        .support-card .label {
            font-size: 0.95rem;
            color: #6c757d;
            line-height: 1.2;
        }

        /* Specific card colors/icons (example) */
        .support-card.email-card .icon-wrapper { background-color: rgba(255, 193, 7, 0.1); }
        .support-card.email-card .icon-wrapper i { color: #ffc107; } /* Yellow for Email */

        .support-card.whatsapp-card .icon-wrapper { background-color: rgba(40, 167, 69, 0.1); }
        .support-card.whatsapp-card .icon-wrapper i { color: #28a745; } /* Green for WhatsApp */

        .support-card.call-card .icon-wrapper { background-color: rgba(23, 162, 184, 0.1); }
        .support-card.call-card .icon-wrapper i { color: #17a2b8; } /* Cyan for Call */

        /* Specific handling for multi-line email/phone links to avoid line breaks within the link */
        .contact-detail a {
            word-break: break-all; /* Allow breaking long email/number */
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h2 class="h4 font-weight-bold mb-4">{{ $title }}</h2>
                <p class="text-muted">Feel free to reach out to us through the following channels:</p>
            </div>
        </div>

        {{-- Support Contact Cards --}}
        <div class="col-lg-4 col-md-6 mb-4"> {{-- Added mb-4 for spacing --}}
            <div class="support-card email-card h-100"> {{-- h-100 to ensure consistent height --}}
                <div class="icon-wrapper">
                    <i data-lucide="mail"></i> {{-- Changed icon to mail --}}
                </div>
                <div class="contact-detail">
                    <a href="mailto:{{ setting('mail') }}">{{ setting('mail') }}</a>
                </div>
                <div class="label">Email ID</div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="support-card whatsapp-card h-100">
                <div class="icon-wrapper">
                    <i data-lucide="message-circle"></i> {{-- Changed icon to message-circle or send --}}
                </div>
                <div class="contact-detail">
                    <a href="{{ setting('whatsapp') }}" target="_blank">Click to Chat with WhatsApp</a>
                </div>
                <div class="label">WhatsApp Support</div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="support-card call-card h-100">
                <div class="icon-wrapper">
                    <i data-lucide="phone"></i> {{-- Changed icon to phone --}}
                </div>
                <div class="contact-detail">
                    <a href="tel:+91{{ setting('number') }}">Click to Call Now</a>
                </div>
                <div class="label">Call Us</div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // No specific JS needed for this page as it's purely for display and linking.
        });
    </script>
@endsection