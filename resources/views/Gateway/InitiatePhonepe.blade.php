<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Verification</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #233142;
    color: #e3e3e3;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  .container {
    background-color: #455d7a;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    max-width: 400px;
    width: 100%;
  }
  h1 {
    text-align: center;
  }
  form {
    margin-top: 20px;
  }
  label {
    display: block;
    margin-bottom: 10px;
  }
  input[type="text"], input[type="password"] {
    width: calc(100% - 20px);
    padding: 10px;
    margin-bottom: 20px;
    border: none;
    border-radius: 5px;
  }
  button {
    background-color: #b80257;
    color: #e3e3e3;
    border: none;
    border-radius: 5px;
    padding: 10px;
    width: 100%;
    cursor: pointer;
  }
  button:hover {
    background-color: #f95959;
  }
  .receipt {
    display: none;
    margin-top: 20px;
    padding: 20px;
    background-color: #301b1b;
    border-radius: 5px;
  }
  .receipt p {
    margin: 5px 0;
  }
  .receipt-button {
    background-color: #b80257;
    color: #e3e3e3;
    border: none;
    border-radius: 5px;
    padding: 10px;
    width: 100%;
    cursor: pointer;
    margin-top: 20px;
  }
  .receipt-button:hover {
    background-color: #f95959;
  }
</style>
</head>
<body>
  <h1>Loading..</h1>
  <script>
      setTimeout(()=>{
          window.location.href="{{$url}}";
      },500);
  </script>
</body>

