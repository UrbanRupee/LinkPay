<!DOCTYPE html>
<html lang="en">

<head>
<?php include 'include/Head.php'; ?>
    
    <title>Privacy Policy - Finixpay services private limited</title>
    <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      line-height: 1.6;
      color: #333;
    }
    h1, h2 {
      text-align: center;
      color: #444;
    }
    .section {
      margin: 20px 0;
    }
    .accordion {
      cursor: pointer;
      padding: 15px;
      background-color: #f7f7f7;
      border: 1px solid #ddd;
      outline: none;
      text-align: left;
      transition: background-color 0.3s ease;
      font-weight: bold;
      width: 100%;
    }
    .accordion:hover {
      background-color: #e7e7e7;
    }
    .panel {
      padding: 10px 15px;
      display: none;
      background-color: #f9f9f9;
      border-top: none;
    }
    .active {
      display: block;
    }
  </style>
</head>

<body class="body-wrapper">    
<?php include 'include/Preloader.php'; ?>

<?php include 'include/Header.php'; ?>

    <div class="page-banner-wrap text-center bg-cover" style="background-image: url('assets/img/page-banner.jpg')">
        <div class="container">
            <div class="page-heading text-white">
                <h1>Privacy Policy</h1>
            </div>
            <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="index">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="section">
    <button class="accordion">Introduction</button>
    <div class="panel">
      <p>
        This Privacy Policy describes how Finixpay Services Private Limited and its affiliates ("we," "our," "us") collect, use, share, and protect your personal data through our website <a href="https://finixpayservices.com" target="_blank">https://finixpayservices.com</a>. 
        By visiting the Platform or availing our services, you agree to be bound by the terms of this Privacy Policy and applicable Indian laws. If you do not agree, please do not use our Platform.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Collection of Personal Data</button>
    <div class="panel">
      <p>
        We collect personal data, such as your name, date of birth, address, email, phone number, payment details, and biometric information (with consent). 
        Information is also collected based on your interactions on the Platform, including transactions and behavior. 
        Please avoid sharing sensitive data such as PINs or passwords with unauthorized entities.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Usage of Personal Data</button>
    <div class="panel">
      <p>
        We use your data to provide services, process transactions, improve customer experience, resolve disputes, prevent fraud, conduct research, and market our offerings. 
        Users may opt out of certain uses by contacting us.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Sharing of Personal Data</button>
    <div class="panel">
      <p>
        Personal data may be shared internally, with affiliates, business partners, or third-party providers for service fulfillment, marketing, or compliance with legal obligations. 
        Disclosures to law enforcement or authorized agencies may occur as required by law.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Security Precautions</button>
    <div class="panel">
      <p>
        We implement reasonable security measures to protect your data. However, data transmission over the internet is not entirely secure, and users are responsible for safeguarding their login credentials.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Data Deletion and Retention</button>
    <div class="panel">
      <p>
        You may delete your account via the Platform settings or by contacting us. 
        Data is retained as needed for legitimate purposes, including fraud prevention or compliance with legal obligations. 
        Anonymized data may be retained for research purposes.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Your Rights</button>
    <div class="panel">
      <p>
        You may access, update, or rectify your personal data through Platform functionalities. 
        Withdrawal of consent may limit service access and must be communicated to our Grievance Officer.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Changes to this Privacy Policy</button>
    <div class="panel">
      <p>
        This Privacy Policy may be updated periodically. Users will be notified of significant changes as required by law.
      </p>
    </div>
  </div>

  <div class="section">
    <button class="accordion">Grievance Officer</button>
    <div class="panel">
      <p>
        <strong>Shivam Pandey</strong><br>
        Designation: Grievance Officer<br>
        Contact: +91 8429549388<br>
        Phone Availability: Monday - Friday (9:00 AM - 6:00 PM)
      </p>
    </div>
  </div>

    <?php include 'include/Footer.php'; ?>
    
    <!--  ALl JS Plugins
    ====================================== -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/modernizr.min.js"></script>
    <script src="assets/js/jquery.easing.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/isotope.pkgd.min.js"></script>
    <script src="assets/js/imageload.min.js"></script>
    <script src="assets/js/scrollUp.min.js"></script>
    <script src="assets/js/slick.min.js"></script>
    <script src="assets/js/slick-animation.min.js"></script>
    <script src="assets/js/magnific-popup.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/metismenu.js"></script>
    <script src="assets/js/nice-select2.js"></script>
    <script src="assets/js/active.js"></script>
    <script>
    const accordions = document.querySelectorAll(".accordion");

    accordions.forEach((accordion) => {
      accordion.addEventListener("click", function () {
        this.nextElementSibling.classList.toggle("active");
      });
    });
  </script>
</body>

</html>