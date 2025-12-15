<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="{{ setting('app_name') }} - Your Modern Payment Gateway">
    <meta name="keywords" content="payment gateway, merchant panel, online payments, digital payments">

    <title>{{ setting('app_name') }} - Login</title>

    <link rel="icon" type="image/jpeg" href="{{ url('assets-home/images/logo/logo.jpeg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-razorpay-blue: #3361E9; /* Dominant blue for actions */
            --light-blue-bg: #EAF0FF; /* Very light blue for background if needed */
            --text-color-dark: #2F363F; /* Dark text for headings */
            --text-color-medium: #4A5568; /* Medium text for body */
            --text-color-light: #718096; /* Lighter text for small info */
            --border-color-light: #E2E8F0; /* Light border for inputs */
            --bg-neutral-light: #F7FAFC; /* Neutral light background for secondary areas */
            --shadow-subtle: 0 4px 12px rgba(0, 0, 0, 0.08); /* Softer shadow */
            --border-radius-lg: 0.75rem; /* Larger border-radius */
            --font-inter: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-inter);
            background-color: var(--bg-neutral-light); /* Or a specific image */
            display: flex;
            justify-content: flex-end;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem; /* Slightly reduced padding */
            color: var(--text-color-dark);
            overflow: hidden; /* Prevent scrollbar from background image */
        }

        /* Full-screen background image for the entire page body */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://accounts.razorpay.com/static/auth/images/auth-bg-img.png'); /* Replace with your desired image URL */
            background-size: cover;
            background-position: center;
            filter: grayscale(10%) brightness(80%); /* Subtle filter to make text pop */
            z-index: -1; /* Place behind content */
        }


        .auth-container {
            background-color: white; /* Only the right form side will be white */
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-subtle);
            max-width: 900px; /* Adjusted max width */
            /*width: 100%;*/
            display: flex;
            overflow: hidden;
            min-height: 550px; /* Ensure sufficient height */
        }

        .auth-image-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            background: transparent; /* Background is now on the body */
            color: white; /* Text color for the left side */
            text-align: left; /* Align text left as per Razorpay */
            flex-direction: column; /* Stack logo and text */
        }

        .auth-image-side .auth-image-content {
            z-index: 2;
            padding: 0 2rem; /* Inner padding */
            max-width: 400px; /* Control width of content */
        }

        .auth-image-side .auth-image-content img {
            max-width: 250px; /* Larger logo on left side */
            margin-bottom: 2.5rem;
            filter: brightness(0) invert(1); /* Keep white for dark background */
        }

        .auth-image-side .auth-image-content h2 {
            font-size: 2.5rem; /* Larger heading */
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .auth-image-side .auth-image-content .feature-list {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }

        .auth-image-side .auth-image-content .feature-list li {
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        .auth-image-side .auth-image-content .feature-list li::before {
            content: '✓'; /* Checkmark icon */
            color: #4CAF50; /* Green checkmark */
            font-size: 1.3rem;
            margin-right: 0.75rem;
            font-weight: 700;
        }


        .auth-form-side {
            flex: 1;
            background-color: white; /* Explicitly white background for the form side */
            padding: 2.5rem; /* Padding inside the card */
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center content vertically */
            position: relative;
            max-width: 450px; /* Fixed width for the form side */
            box-shadow: var(--shadow-subtle); /* A distinct shadow for the form card itself */
        }

        .auth-form-side .form-header-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .auth-form-side .form-header-logo img {
            max-width: 80%; /* Small logo at the top of the form */
            height: auto;
        }
        .auth-form-side .form-header-logo + h5 { /* Adjust heading right after logo */
            margin-top: 1rem;
        }

        h5.auth-heading {
            font-size: 1.6rem; /* Form heading size */
            font-weight: 600;
            color: var(--text-color-dark);
            margin-bottom: 0.75rem; /* Closer to the form elements */
            text-align: center;
        }
        .auth-form-side p.auth-subheading {
            font-size: 0.95rem;
            color: var(--text-color-medium);
            margin-bottom: 2rem;
            text-align: center;
            line-height: 1.4;
        }

        .form-group label {
            display: none; /* Hide labels as per Razorpay design */
        }
        .form-control {
            border-radius: 0.4rem; /* Slightly smaller border-radius for inputs */
            padding: 0.9rem 1rem; /* More padding */
            border: 1px solid var(--border-color-light);
            font-size: 0.95rem;
            color: var(--text-color-dark);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--primary-razorpay-blue);
            box-shadow: 0 0 0 0.2rem rgba(51, 97, 233, 0.25); /* Focus shadow for primary blue */
        }
        .form-control::placeholder {
            color: #A0AEC0; /* Lighter placeholder */
            opacity: 1;
        }

        .btn-primary {
            background-color: var(--primary-razorpay-blue);
            border-color: var(--primary-razorpay-blue);
            border-radius: 0.4rem;
            padding: 0.9rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
            margin-top: 1.5rem;
            box-shadow: 0 2px 6px rgba(51, 97, 233, 0.2);
        }
        .btn-primary:hover {
            background-color: #2a52d3; /* Darker blue on hover */
            border-color: #2a52d3;
            box-shadow: 0 4px 10px rgba(51, 97, 233, 0.3);
        }
        .btn-primary:active {
            box-shadow: none;
        }

        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 2rem 0; /* Space around the separator */
            color: var(--text-color-light);
        }
        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color-light);
        }
        .separator:not(:empty)::before {
            margin-right: .75em;
        }
        .separator:not(:empty)::after {
            margin-left: .75em;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.85rem 1.5rem;
            border: 1px solid var(--border-color-light);
            border-radius: 0.4rem;
            background-color: white;
            color: var(--text-color-medium);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-google .material-symbols-outlined {
            margin-right: 0.75rem;
            font-size: 1.25rem;
            color: #EA4335; /* Google red */
        }
        .btn-google:hover {
            background-color: var(--bg-neutral-light);
            border-color: #D1D9E0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .form-footer-legal-text {
            font-size: 0.75rem;
            color: var(--text-color-light);
            text-align: center;
            margin-top: 2.5rem; /* Space below button */
            line-height: 1.4;
        }
        .form-footer-legal-text a {
            color: var(--primary-razorpay-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .form-footer-legal-text a:hover {
            text-decoration: underline;
        }

        /* Error messages */
        .text-danger {
            color: #ef4444 !important; /* A slightly softer red for errors */
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: block;
            text-align: left; /* Ensure error messages align with input */
        }
        .form-check-label {
            color: var(--text-color-secondary);
            font-size: 0.9rem;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) { /* Medium devices and smaller */
            body::before {
                display: none; /* Hide full background image on small screens */
            }
            body {
                background-color: var(--bg-neutral-light); /* Fallback background for small screens */
            }

            .auth-container {
                flex-direction: column; /* Stack image and form vertically */
                max-width: 400px; /* Narrower card on small screens */
                min-height: auto; /* Auto height */
                box-shadow: var(--shadow-subtle);
            }
            .auth-image-side {
                display: none; /* Hide image side completely on small screens */
            }
            .auth-form-side {
                width: 100%; /* Full width of its container */
                max-width: 100%; /* Ensure it doesn't break */
                padding: 2rem; /* Adjusted padding */
                box-shadow: none; /* No extra shadow for inner form card */
            }
            .auth-form-side .form-header-logo {
                margin-bottom: 1.25rem;
            }
            h5.auth-heading {
                font-size: 1.4rem;
                margin-bottom: 0.5rem;
            }
            p.auth-subheading {
                margin-bottom: 1.5rem;
            }
            .separator {
                margin: 1.5rem 0;
            }
            .form-footer-legal-text {
                margin-top: 2rem;
            }
            .mb-3 { /* General margin-bottom for form groups */
                margin-bottom: 1rem !important;
            }
        }
    </style>
    @yield('css')
</head>

<body style="background: url('/assets/images/razorpay-bg-visual-1.3x.jpeg');background-position: top;">
    <div class="auth-container">
        <div class="auth-form-side">
            <div class="form-header-logo">
                <img src="/assets-home/images/logo/logo.jpeg" alt="{{ setting('app_name') }} Logo" />
            </div>
            <h5 class="auth-heading">Welcome to {{ setting('app_name') }}</h5>
            <p class="auth-subheading">Get started with your email or phone number</p>
            @yield('content')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
        integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-FxH8tP3Kj9/QfL1Z1H9g2L8t4WdKjJ6U1Q2p4Z5g5L5z5q5V5v5z5W5" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"
        integrity="sha512-rstIgDs0xPgmG6RX1Aba4KV5cWJbAMcvRCVmglpam9SoHZiUCyQVDdH2LPlxoHtrv17XWblE/V/PP+Tr04hbtA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <script src="/admin_assets/js/main.js"></script>
    <script src="/admin_assets/js/sweet-alert.js"></script>

    @yield('js')
</body>

</html>