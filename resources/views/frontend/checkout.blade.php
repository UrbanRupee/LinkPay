<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>xpaisa</title>
  <link rel="icon" type="image/png" href="{{ asset('assets-home/images/logo/logo.jpeg') }}">

<style>*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family: 'Nunito Sans', sans-serif;
}

:root{
  --ur-primary:#5de0e6;
  --ur-primary-600:#3bbdc4;
  --ur-bg:#F8FAFC;
  --ur-card:#FFFFFF;
  --ur-text:#0F172A;
  --ur-muted:#64748B;
  --ur-border:#E2E8F0;
}

body{background-color:var(--ur-bg);color:var(--ur-text)}

.site-header{background:#e5e7eb;color:#0F172A;padding:18px 0;border-bottom:1px solid rgba(0,0,0,.06)}
.nav{max-width:980px;margin:0 auto;padding:0 16px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;align-items:baseline;gap:8px}
.brand img.brand-logo{height:28px;width:auto;display:block}
.brand h1{font-size:22px;font-weight:700;letter-spacing:.4px}
.brand span{font-size:13px;opacity:.9}

.container{max-width:980px;margin:24px auto 40px;padding:0 16px}
.grid{display:grid;grid-template-columns:1.3fr .7fr;gap:20px}
.card{background:var(--ur-card);border:1px solid var(--ur-border);border-radius:12px;box-shadow:0 2px 14px rgba(15,23,42,.06)}
.card .card-head{padding:18px 20px 8px;border-bottom:1px solid var(--ur-border)}
.card .card-head h2{font-size:18px;font-weight:700}
.card .card-body{padding:18px 20px 22px}
.notice{margin-bottom:12px;color:#DC2626;font-size:13px}

form .row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-field{display:flex;flex-direction:column;gap:8px;margin-top:12px}
label{font-size:14px;color:var(--ur-muted)}
input[type="text"],input[type="tel"],input[type="number"]{height:44px;font-size:15px;padding:10px 12px;border:1px solid var(--ur-border);border-radius:8px;outline:none;transition:border-color .15s ease,box-shadow .15s ease}
input[type="text"]:focus,input[type="tel"]:focus,input[type="number"]:focus{border-color:var(--ur-primary);box-shadow:0 0 0 3px rgba(93,224,230,.12)}
.submit{width:100%;height:46px;border:none;border-radius:8px;background:var(--ur-primary);color:#fff;font-size:16px;font-weight:700;margin-top:18px;cursor:pointer;transition:transform .05s ease,opacity .2s ease,background .2s ease}
.submit:hover{opacity:.95}
.submit:active{transform:translateY(1px)}
.submit[disabled]{opacity:.7;cursor:not-allowed}
.secure{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ur-muted);margin-top:10px}
.summary-list{list-style:none;display:grid;gap:10px}
.summary-item{display:flex;align-items:center;justify-content:space-between;font-size:14px}
.summary-item .muted{color:var(--ur-muted)}
.summary-item .value{font-weight:700}
.badges{margin-top:12px;border-top:1px solid var(--ur-border);padding-top:14px;text-align:center}
.badges img{width:100%;max-width:360px;opacity:.85}
.help{font-size:12px;color:var(--ur-muted);text-align:center;margin-top:12px}

/* QR box styling */
.qr-box{width:280px;height:280px;margin:0 auto;display:flex;align-items:center;justify-content:center;background:#fff;border:1px solid var(--ur-border);border-radius:12px;box-shadow:0 2px 10px rgba(15,23,42,.06);overflow:hidden}
.qr-box .placeholder{color:var(--ur-muted);font-size:12px;text-align:center;padding:12px}

@media (max-width:860px){.grid{grid-template-columns:1fr}}
</style>

  <script>
  window.console = window.console || function(t) {};
</script>
</head>

<body>
  <header class="site-header">
    <div class="nav">
      <div class="brand">
        <img class="brand-logo" src="{{ asset('assets-home/images/logo/logo.jpeg') }}" alt="xpaisa">
        <h1>xpaisa</h1>
        
      </div>
      <div class="secure" aria-label="Secure checkout">🔒 128-bit SSL secured</div>
    </div>
  </header>

  <main class="container">
    <div class="grid">
      <div class="card">
        <div class="card-head"><h2>Payment details</h2></div>
        <div class="card-body">
          <div style="display:flex;gap:8px;margin-bottom:8px;">
            <button type="button" class="submit" style="height:36px;width:auto;padding:0 12px;background:#0ea5e9" data-method="card">💳 Card</button>
            <button type="button" class="submit" style="height:36px;width:auto;padding:0 12px;background:#0F766E" data-method="upi">UPI</button>
          </div>
          <form action="/api/pg/phonepe/initiate" method="post" id="checkoutForm">
            <p class="notice">{{ session()->has('error') ? session()->get('error') : '' }}</p>
            <input type="hidden" name="seamless" value="false">
            <input type="hidden" name="userid" value="{{$userid}}">
            <input type="hidden" name="token" value="{{$token}}">
            <input type="hidden" name="orderid" value="{{$userid.date('YmdHis').rand(10000,99999)}}">
            <input type="hidden" name="callback_url" value="https://merchant.rudraxpay.com">
            <input type="hidden" name="_method" id="selectedMethod" value="upi">
            <input type="hidden" name="currency" value="INR">
            <input type="hidden" name="reference" id="referenceField" value="{{$userid.date('YmdHis').rand(10000,99999)}}">
            <input type="hidden" name="ip_address" id="ip_address" value="">

            <div class="row">
              <div class="form-field">
                <label for="name">Full name</label>
                <input id="name" type="text" name="name" placeholder="e.g. {{$name}}" autocomplete="name" required>
              </div>
              <div class="form-field">
                <label for="mobile">Mobile number</label>
                <input id="mobile" type="tel" name="mobile" placeholder="e.g. 9876543210" pattern="[6-9]{1}[0-9]{9}" inputmode="numeric" autocomplete="tel" required>
              </div>
            </div>

            <div class="form-field">
              <label for="amount">Amount (INR)</label>
              <input id="amount" type="number" name="amount" placeholder="Enter amount" min="1" step="1" inputmode="numeric" required>
            </div>

            <div id="cardFields" style="display:none;">
              <div class="row">
                <div class="form-field">
                  <label for="firstname">First name</label>
                  <input id="firstname" name="firstname" type="text" placeholder="First name">
                </div>
                <div class="form-field">
                  <label for="lastname">Last name</label>
                  <input id="lastname" name="lastname" type="text" placeholder="Last name">
                </div>
              </div>
              <div class="row">
                <div class="form-field">
                  <label for="email">Email</label>
                  <input id="email" name="email" type="text" placeholder="name@example.com">
                </div>
                <div class="form-field">
                  <label for="phone">Phone</label>
                  <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="10" placeholder="9876543210">
                </div>
              </div>
              <div class="row">
                <div class="form-field">
                  <label for="cardName">Name on card</label>
                  <input id="cardName" name="cardName" type="text" placeholder="As printed on card">
                </div>
                <div class="form-field">
                  <label for="cardNumber">Card number</label>
                  <input id="cardNumber" name="cardNumber" type="text" inputmode="numeric" maxlength="19" placeholder="XXXX XXXX XXXX XXXX">
                </div>
              </div>
              <div class="row">
                <div class="form-field">
                  <label for="expMonth">Expiry (MM)</label>
                  <input id="expMonth" name="expMonth" type="text" inputmode="numeric" maxlength="2" placeholder="MM">
                </div>
                <div class="form-field">
                  <label for="expYear">Expiry (YY)</label>
                  <input id="expYear" name="expYear" type="text" inputmode="numeric" maxlength="2" placeholder="YY">
                </div>
              </div>
              <div class="row">
                <div class="form-field">
                  <label for="cardCVV">CVV</label>
                  <input id="cardCVV" name="cardCVV" type="password" inputmode="numeric" maxlength="4" placeholder="CVV">
                </div>
                <div class="form-field">
                  <label for="country">Country</label>
                  <input id="country" name="country" type="text" value="IN" placeholder="IN">
                </div>
              </div>
              <div class="row">
                <div class="form-field">
                  <label for="state">State</label>
                  <input id="state" name="state" type="text" placeholder="State">
                </div>
                <div class="form-field">
                  <label for="city">City</label>
                  <input id="city" name="city" type="text" placeholder="City">
                </div>
              </div>
              <div class="row">
                <div class="form-field">
                  <label for="address">Address</label>
                  <input id="address" name="address" type="text" placeholder="Address line">
                </div>
                <div class="form-field">
                  <label for="zip_code">ZIP</label>
                  <input id="zip_code" name="zip_code" type="text" placeholder="PIN/ZIP code">
                </div>
              </div>
              <p class="help">Note: Card payments are secured with additional checks. Ensure details are accurate.</p>
            </div>


            <button type="submit" class="submit" id="payBtn">Proceed to pay</button>
            <div class="secure">🔐 Your payment is processed securely</div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h2>UPI QR</h2></div>
        <div class="card-body">
          <div id="upiQRBox" style="display:block;">
            <div class="form-field">
              <div id="qrCanvas" class="qr-box"><span class="placeholder"></span></div>
            </div>
          </div>
          <ul class="summary-list" style="margin-top:16px;">
            <li class="summary-item"><span class="muted">Payee</span> <span class="value">{{$name}}</span></li>
            <li class="summary-item"><span class="muted">User ID</span> <span class="value">{{$userid}}</span></li>
            <li class="summary-item"><span class="muted">Currency</span> <span class="value">INR</span></li>
          </ul>
          <div class="badges">
            <img src="https://content.invisioncic.com/p289038/monthly_2022_10/Payment-methods.png.2b9ba23475aaa15189f555f77ec3a549.png" alt="Supported payment methods">
            <p class="help">Need help? <strong>support@xpaisa.in</strong></p>
          </div>
        </div>
      </div>

    </div>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    (function(){
      var form=document.getElementById('checkoutForm');
      var btn=document.getElementById('payBtn');
      var methodInput=document.getElementById('selectedMethod');
      var cardFields=document.getElementById('cardFields');
      var qrBox=document.getElementById('upiQRBox');
      var qrNode=document.getElementById('qrCanvas');
      var currentQR=null;
      var ipInput=document.getElementById('ip_address');
      var referenceField=document.getElementById('referenceField');

      // Render a default placeholder QR so something is visible by default
      try{
        if(qrNode && typeof QRCode!=='undefined'){
          var defaultUpi = 'upi://pay?pa=xpaisa@upi&pn=xpaisa&am=1&cu=INR&tn=Scan%20after%20generating%20link';
          currentQR = new QRCode(qrNode,{text:defaultUpi,width:260,height:260});
        }
      }catch(e){}

      try{ fetch('https://api.ipify.org?format=json').then(function(r){return r.json();}).then(function(j){ if(ipInput){ ipInput.value=j.ip; } }); }catch(e){}

      Array.prototype.slice.call(document.querySelectorAll('button[data-method]')).forEach(function(btnTab){
        btnTab.addEventListener('click',function(){
          var m=this.getAttribute('data-method');
          methodInput.value=m;
          cardFields.style.display=(m==='card')?'block':'none';
          qrBox.style.display=(m==='upi')?'block':'none';
        });
      });

      if(!form||!btn)return;
      form.addEventListener('submit', async function(e){
        e.preventDefault();
        btn.setAttribute('disabled','disabled');
        btn.textContent='Processing...';

        try{
          var fd=new FormData(form);
          var endpoint='/api/pg/phonepe/initiate';
          if(methodInput.value==='card'){
            endpoint='/api/card/initiate';
            fd.set('reference', referenceField.value);
          }

          const primary = await fetch(endpoint,{ method:'POST', headers:{ 'Accept':'application/json' }, body:fd });
          const data = await primary.json();

          if(!data||data.status!==true){
            throw new Error((data&&data.message)||'Unable to generate payment link.');
          }

          let url = data.url || '';

          if(methodInput.value==='upi'){
            var orderId = form.querySelector('input[name="orderid"]').value;
            var isUpiDeepLink = /^upi:\/\//i.test(url);
            if(!isUpiDeepLink && orderId){
              try{
                const qp = await fetch('/api/gateway/qutepaisa/payin/' + encodeURIComponent(orderId), { method:'POST', headers:{ 'Accept':'application/json' } });
                const qpData = await qp.json();
                if(qpData && qpData.status===true && qpData.url){
                  url = qpData.url;
                }
              }catch(ignore){}
            }

            if(!url){ throw new Error('No payment link received.'); }

            if(currentQR){ qrNode.innerHTML=''; currentQR=null; }
            try{ currentQR=new QRCode(qrNode,{text:url,width:260,height:260}); }catch(err){ qrNode.textContent='QR error'; }
            qrBox.style.display='block';
          }else{
            if(url){ window.location.href=url; }
          }
        }catch(err){
          alert(err && err.message ? err.message : 'Network error. Please try again.');
        }finally{
          btn.removeAttribute('disabled');
          btn.textContent='Proceed to pay';
        }
      });
    })();
  </script>
</body>

</html>