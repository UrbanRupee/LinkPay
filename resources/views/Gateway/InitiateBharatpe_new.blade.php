
<html translate="no" class="">
    <head>
        <meta charset="utf-8">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="Cache-Control" content="no-store, must-revalidate">
        <meta http-equiv="expires" content="0">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"><!--[if IE]><link rel="icon" href="/favicon.ico"><![endif]-->
        <title>
            Pay-In
        </title>
        <link href="/mannualGatewayAssets/css/IndiaQR.css" rel="prefetch">
        <link href="/mannualGatewayAssets/css/IndiaQR~NotSkipUpi.css" rel="prefetch">
        <link href="/mannualGatewayAssets/css/app.css" rel="preload" as="style">
        <link href="/mannualGatewayAssets/css/chunk-elementUI.css" rel="preload" as="style">
        <link href="/mannualGatewayAssets/css/chunk-libs.css" rel="preload" as="style">
        <link href="/mannualGatewayAssets/css/chunk-elementUI.css" rel="stylesheet" type="text/css">
        <link href="/mannualGatewayAssets/css/chunk-libs.css" rel="stylesheet" type="text/css">
        <link href="/mannualGatewayAssets/css/app.css" rel="stylesheet" type="text/css">
        <meta name="theme-color" content="#4DBA87">
        <meta name="apple-mobile-web-app-capable" content="no">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="checkstand">
        <meta name="msapplication-TileColor" content="#000000">
        <link rel="shortcut icon" type="image/x-icon" sizes="32x32" href="null">
        <link rel="stylesheet" type="text/css" href="/mannualGatewayAssets/css/IndiaQR~NotSkipUpi.css">
        <link rel="stylesheet" type="text/css" href="/mannualGatewayAssets/css/IndiaQR.css">
        <link rel="stylesheet" type="text/css" href="/mannualGatewayAssets/css/toast.css">
    </head>
    <body class="">
        <noscript><strong>We're sorry but checkstand doesn't work properly without JavaScript enabled. Please enable it to continue.</strong></noscript>
        <div id="app">
            <div  class="app-layout">
                <!---->
                <div   class="app-mobile" style="padding-top: 20px;">
                    <div   class="container">
                        <div   class="india-qr-h5">
                            <div  class="utr">
                                <div  class="utr-header">
                                    <div  class="utr-header-info">
                                        <div  class="utr-header-info-title">
                                            Amount
                                        </div>
                                        <div  class="utr-header-info-amount">
                                            <span  style="font-family: auto;">₹</span> <span id="amountTxt1">{{$dATA->amount}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div  class="utr-main">
                                    <div id="utr-main-qr" class="utr-main-qr">
                                        <div  class="utr-main-qr-title">
                                            UPI QR code
                                        </div>
                                        <div class="qr qr-code-container" id="qrcode" style="width: 17em; height: 17em;">
                                            <img style="width: 15em; height: 15em;">
                                        </div>
                                        <div  class="utr-main-qr-text">
                                            Important! The UTR must be entered manually!
                                        </div>
                                    </div>
                                    <div  class="utr-main-self">
                                        <div  class="utr-main-self-info" style="display:none;">
                                            <div   class="form-item-row" style="border-bottom: 1px solid rgb(231, 231, 246);">
                                                <div  class="form-item-row-label">
                                                    UPI ID
                                                </div>
                                                <div  class="form-item-row-value">
                                                    <span id="upiTxt" style="max-width: 260px; word-break: break-all; line-height: 1.5em; text-align: right;"></span><span id="upi" style="color: blue; margin-left: 5px;">copy</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div  class="utr-input-show">
                                            <div  class="el-input el-input--small">
                                                <input id="input_utr" type="number" autocomplete="off" maxlength="12" onkeyup="value=value.replace(/[^\d]/g,'')" placeholder="UTR is mandatory" class="el-input__inner">
                                            </div>
                                            <button  type="button" class="el-button utrSubmit el-button--primary el-button--mini"><span>SUBMIT</span></button>
                                        </div>
                                        <p class="utr-find-show" style="text-align: center; font-size: 16px;">
                                            <span  style="border-bottom: 1px solid red;">How to find your UTR?</span>
                                        </p>
                                        <p  style="color: red;">
                                            Important reminder: After completing the UPI transaction, please backfill Ref No./UTR No./Google Pay : UPI Transaction ID/Freecharge: Transaction ID (12digits). If you do not backfill UTR, 100% of the deposit transaction will fail. Please be sure to backfill!
                                        </p>
                                        <img src="/mannualGatewayAssets/img/1.jpeg" style="width:30%;">
                                        <img src="/mannualGatewayAssets/img/2.jpeg" style="width:30%;">
                                        <img src="/mannualGatewayAssets/img/3.jpeg" style="width:30%;">
                                        <div  class="support-bank-list">
                                            <div  style="text-align: center;">
                                                <span >Paytm,</span> <span >GPay,</span> <span >BHIM &amp; More</span>
                                            </div>
                                            <div  class="logo" style="text-align: center; margin-top: 10px;">
                                                <span  style="display: inline-block; margin-left: 10px;"><img  src="/mannualGatewayAssets/img/paytm.png" style="width: 30px;"></span><span  style="display: inline-block; margin-left: 10px;"></span><span  style="display: inline-block; margin-left: 10px;"><img  src="/mannualGatewayAssets/img/g-pay.png" style="width: 30px;"></span><span  style="display: inline-block; margin-left: 10px;"><img  src="/mannualGatewayAssets/img/bhim.png" style="width: 30px;"></span><span  style="display: inline-block; margin-left: 10px;"><img  src="/mannualGatewayAssets/img/india.png" style="width: 30px;"></span><span  style="display: inline-block; margin-left: 10px;"><img  src=
                                                "data:image/png;base64,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"
                                                style="width: 30px;"></span><span  style="display: inline-block; margin-left: 10px;"><img  src="/mannualGatewayAssets/img/india-f.png" style="width: 30px;"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="el-button utr-main-btn el-button--primary el-button--medium" style="display:none;"><!----><!----> <span>Click &amp; Choose your app to pay ₹<span id="amountTxt2">??</span></span></button>
                                </div>
                            </div>
                            <div  tabindex="-1" class="el-drawer__wrapper el-drawer-fade-enter-active el-drawer-fade-enter-t" style="z-index: 2009; display: none;">
                                <div role="document" tabindex="-1" class="el-drawer__container">
                                    <div aria-modal="true" aria-labelledby="el-drawer__title" aria-label="" role="dialog" tabindex="-1" class="el-drawer btt" style="height: 35%;">
                                        <!---->
                                        <div  class="bank-list">
                                            <!--<div  class="bank-item" id="bank1">-->
                                            <!--    <img  src="/mannualGatewayAssets/img/paytm.png"> Paytm-->
                                            <!--</div>-->
                                            <!--<div  class="bank-item" id="bank2">-->
                                            <!--    <img  src="/mannualGatewayAssets/img/phone-pe.png"> PhonePe-->
                                            <!--</div>-->
                                            <!--<div  class="bank-item" id="bank3">-->
                                            <!--    <img  src="/mannualGatewayAssets/img/g-pay.png"> GPay-->
                                            <!--</div>-->
                                            <!--<div  class="bank-item" id="bank4">-->
                                            <!--    <img  src="data:image/png;base64,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"> MobiKwik-->
                                            <!--</div>-->
<!--                                            <div  class="bank-item" id="bank5">-->
<!--                                                <img  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAACE1BMVEUAAAD/AAAAgIAAgP8AVVUAqqr/VVUAgIAAv7//gIAAzMz/ZmYAgIAAcXH/cVUAgIAAs8wAgIDqamrtbVsAu8wAr88AuNUAvMnya14AgHMAs8wAeXkAts4Aenr0b2QAtcr0dWAAuMwAus4As9AAttH2bWQAucoAu8wAtc4AeHgAeHjwcWIAtM0AuNAAtcwAt80AuM4AfXj1c18At88AuNAAts0At84AeXnycF8AfHj0cWIAts0At87ycl8AuM8Ats8Ae3gAt80At80Ae3gAt8/zcmAAencAts0At84AuM8Ats8Ae3jzcV8AengAts7ycmAAt84At88AuM8At84AuM4Ats4At88At80AuM4Ats4At84At84At84AfHgAuM70cWAAe3gAt84Ats7zcV8At88Ae3gAt80AuM4Ats4At84Ae3gAt8/zcWEAe3cAt84Ae3jzcWAAt84AencAt87zcWAAts8At80At84AuM4Ae3jzcV8At84At80At84At84AuM4At84At88Ae3jzcWAAt84At84At84At87zcWAAe3gAe3jzcWD0cWAAe3gAfHgAts4At84At84At87zcWDzcWAAt84Ae3gAt84At84At84Ae3gAt87zcWAAe3fzcWAAt84At870cWAAe3gAe3gAt84At84At84At87zcV8At84At84Ae3gAt84Ae3gAt87zcWD///8++VW9AAAArXRSTlMAAQICAwMDBAQEBQUICQkKCgwMDg8QEhMTFBQVFRcXGBgZGhscHB0eHyAiIikrLS4vMzM1Njg5OztERE1OTk9QUVFSVVVVVldYWltmZnd3d3h5enx9fn+AgYKDhoeIiIiRkZOTlJWVlpeYmZmZmpuqqqutra2ztLW2u7u9vr/AwcLDzMzR0tTV19rd3d7f4eHi4+Tk6Orr6+zt7u7u7/Lz9PT2+Pj5+vv7/P3+/nsYrfsAAAABYktHRLBDZK7EAAACkElEQVQ4y4WV6V8SQRjHp6g8uujSLi1cEysR7zJMyiQtz1CplKyMFIJSIiMtuzy6C7uQrLQCUwv2X2yeZ5Z2WJZ6XszszHw/8zzzzDy/JYS31VVW35PZhYXZKZ+1UkNSWUHv2xhn0/Z8VWy3OxJTWMS5M5k7FYqp2EyjAsu8wRaW7naYivT6IlPn2BKbcWXynPYOTs6d08lzed3zOOnXcvshtzywJ9GNzv0b5m/Le6LfuZrkwOtwU2d8eBpGgWK1VJRMw1oDG2QHYT+jem5LYM8Qy5Ib4qtJdQt1EGc/3gfkeSCZ2G7xvMylvYMuh+GOesGx4rwZZW2ji6IoArgNbsJO3wHc73meOtg89FVkBiA5C0fVkCra/fyb570WzztRNgTzlylSQay0vYfQmqNXnoq8fb61GRceUaSd+GjbgeNcHlocv3hkreQGfHvJFG1NCvCFx7KFC9pMkUkCZzJw4Puh5n2KRBkpEiSQxQIJ/DbaVrZCBtIOrcdeD5nkwA2HM7htDjQNfonmcKDsWrZdJ669joIxsBhdy4dhtrH6wuNfUckYWIuHgfR0StCq0paRH1HOGGjD9Jyh7RiON938xEPRZ1ePpeHCA4q0kkooKbzCHA76ONi0Px6MAGVWTjTwhrt58PtIS+lKLmrw/IbKhp3287o4+PxS9brEFOAz64HHAZl0Afjq+skdyQ8YH64AX04oheP/LIU+/Mz6b3HNSBLUiOVqTF2u9fGhCwXAnEoAHLKk+FFSLm9VnNeBkjKczomUn4lcV548J9iYEA5rE2TPKcne/a5aQ2GhwWx7KMmeI10RTkNQTUg/1CcHnt0fVmLhvizVnAn2AI8FeoSU/wVNRbt3MhSJhCa8reWJv48/eVuuwyRcwuAAAAAASUVORK5CYII="> BharatPe-->
<!--                                            </div>-->
                                            <div  class="bank-item" id="bank6">
                                                <img  src="/mannualGatewayAssets/img/bhim.png"> BHIM
                                            </div>
                                            <!--<div  class="bank-item"  id="bank7">-->
                                            <!--    <img  src="data:image/png;base64,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"> Freecharge-->
                                            <!--</div>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div  tabindex="-1" class="el-drawer__wrapper" style="z-index: 2013; display: none;">
                                <div role="document" tabindex="-1" class="el-drawer__container">
                                    <div aria-modal="true" aria-labelledby="el-drawer__title" aria-label="" role="dialog" tabindex="-1" class="el-drawer btt" style="height: 80%;">
                                        <header id="el-drawer__title" class="el-drawer__header">
                                            <span role="heading" title=""></span>
                                            <button aria-label="close drawer" type="button" class="el-drawer__close-btn"><i class="el-dialog__close el-icon el-icon-close"></i></button>
                                        </header>
                                        <section class="el-drawer__body">
                                            <div  class="utr-input">
                                                <p  class="utr-title">
                                                    Enter The UTR Number
                                                </p>
                                                <p  class="utr-text">
                                                    Please enter the 12-digit UPI Ref No after payment
                                                </p>
                                                <div  class="el-input el-input--small">
                                                    <input id="input_utr" type="number" autocomplete="off" maxlength="12" onkeyup="value=value.replace(/[^\d]/g,'')" placeholder="UTR is mandatory" class="el-input__inner">
                                                </div>
                                                <div  class="utr-input-btn">
                                                    SUBMIT
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                            <div  tabindex="-1" class="el-drawer__wrapper" style="display: none;">
                                <div role="document" tabindex="-1" class="el-drawer__container">
                                    <div aria-modal="true" aria-labelledby="el-drawer__title" aria-label="" role="dialog" tabindex="-1" class="el-drawer ttb" style="height: 100%;">
                                        <div class="el-loading-mask">
                                            <div class="el-loading-spinner .el-icon-loading">
                                                <p style="color: rgb(95, 175, 255);">In processing... Please be patient</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div tabindex="-1" class="el-drawer__wrapper" style="z-index: 2015; display: none;">
                                <div role="document" tabindex="-1" class="el-drawer__container el-drawer__open">
                                    <div aria-modal="true" aria-labelledby="el-drawer__title" aria-label="" role="dialog" tabindex="-1" class="el-drawer ttb" style="height: 100%;">
                                        <header id="el-drawer__title" class="el-drawer__header">
                                            <span role="heading" title=""></span>
                                            <button aria-label="close drawer" type="button" class="el-drawer__close-btn"><i class="el-dialog__close el-icon el-icon-close"></i></button></header>
                                            <section class="el-drawer__body">
                                                <div style="text-align: center; margin-top: 5px; margin-bottom: 15px;">
                                                    <img src="/mannualGatewayAssets/img/utr1.png" style="width: 100%; margin: 2px 0;">
                                                    <img src="/mannualGatewayAssets/img/utr2.png" style="width: 100%; margin: 2px 0;">
                                                    <img src="/mannualGatewayAssets/img/utr3.png" style="width: 100%; margin: 2px 0;">
                                                </div>
                                            </section>
                                    </div>
                                </div>
                            </div>
                            <div class="v-modal" tabindex="0" style="z-index: 2000; display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div tabindex="-1" role="dialog" aria-modal="true" aria-label="dialog" class="el-message-box__wrapper" style="z-index: 2019; display: none;">
            <div class="el-message-box el-message-box--center">
                <div class="el-message-box__header">
                    <div class="el-message-box__title">
                        
                    </div>
                </div>
                <div class="el-message-box__content">
                    <div class="el-message-box__container">
                        <div class="el-message-box__message">
                            <p style="color: red;font-weight: 500;">
                                Important reminder: After completing the UPI transaction, please backfill Ref No./UTR No./Google Pay : UPI Transaction ID/Freecharge: Transaction ID (12digits). If you do not backfill UTR, 100% of the deposit transaction will fail. Please be sure to backfill!
                            </p>
                        </div>
                    </div>
                    <div class="el-message-box__input" style="display: none;">
                        <div class="el-input">
                            <input type="text" autocomplete="off" placeholder="" class="el-input__inner">
                        </div>
                        <div class="el-message-box__errormsg" style="visibility: hidden;"></div>
                    </div>
                </div>
                <div class="el-message-box__btns">
                    <button type="button" class="el-button el-button--default el-button--small el-button--primary"> <span>I know and continue to pay</span></button>
                </div>
            </div>
        
        </div>
        <div class="v-modal" tabindex="0" style="z-index: 2000; display: none;"></div>
    </body>
</html>
<script src="/mannualGatewayAssets/js/jquery-2.1.4.min.js"></script>
<script src="/mannualGatewayAssets/js/toast.js"></script>
<script src="/mannualGatewayAssets/js/qrcode.min.js"></script>
<script type="text/javascript">
let urlLink = {
    "bank1":"paytmmp://pay",
    "bank2":"phonepe://pay",
    "bank3":"gpay://upi/pay",
    "bank4":"mobikwik://open",
    "bank5":"phonepe://upi/pay",
    "bank6":"upi://mandate",
    "bank7":"freecharge://",
}

//生成qrcode
let create_qrcode = function(text, typeNumber, errorCorrectionLevel) {
    qrcode.stringToBytes = qrcode.stringToBytesFuncs["UTF-8"];

    var qr = qrcode(typeNumber || 0, errorCorrectionLevel || 'H');
    qr.addData(text);
    qr.make();

    return qr.createImgTag(4);
};

function getUrlParam(subStr,name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = subStr.substr(1).match(reg);
    if(r != null) return unescape(r[2]);
    return null;
}
let sn = getUrlParam(window.location.search,"sn");
let upi = "";
let acc = "";
let originalLink1 = "";
let setSearchURL = function(url){
    let amount = {{$amount}};
    amount = (amount).toFixed(2);
    upi = "{{$upid}}";
    let accName = getUrlParam(url,"accName");
    document.getElementById("amountTxt1").innerHTML = amount;
    document.getElementById("amountTxt2").innerHTML = amount;
    document.getElementById("upiTxt").innerHTML = upi;
    setInterval(checkAccount,10000);
    if(amount > 20000000){
        document.getElementById("utr-main-qr").remove();
    }else{
        //document.getElementById("orderIdTxt").innerHTML = sn;
        let sign = getUrlParam(url,"sign");
        originalLink1 = "{{($url)}}";
        
        original_link = "{{$url}}";
        let qrcodeContainer =  $(".qr-code-container");
        qrcodeContainer.empty();
        qrcodeContainer.html(create_qrcode(original_link));
        qrcodeContainer.find("img").attr("alt", original_link);
        qrcodeContainer.show();
    }
}
    orderID = getUrlParam("{{$url}}","tn");;
    setSearchURL("{{$url}}");

//utr 提交
$(".utrSubmit, .utr-input-btn").click(function(){
    let utr = $("#input_utr").val();
    if (utr.length != 12) {
        //Please enter 12 digits
        let msg = 'Please enter 12 digits';
        let type =  'error';
        Toast.init();
        Toast.show(msg, type, null);
        return false;
    }
    let params = {
        sn: "{{$trn_id}}",
        utr: utr,
    };
    $.post("/api/bharatpe/checkUTR", params, function (result) {
        console.log("supplement",result);
        if (!result.status) {
            let type =  'success';
            Toast.init();
            Toast.show(result.message, type, null);
            // window.alert(result.msg || "Success! We have recived your payment under this UTR.");
        } else {
            let type =  'error';
            Toast.init();
            Toast.show(result.message, type, null);
        }
            window.location = result.url;
    });
});


//加载时 显示确认框
function init () {
    $('.el-message-box__wrapper').toggle();
    $('.el-message-box__wrapper').next().toggle();
}
//init ();

$('.el-message-box__wrapper button').click(function(){
    $('.el-message-box__wrapper').toggle();
    $('.el-message-box__wrapper').next().toggle();
})

//底部滑出 图标列表
$('.utr-main-btn').click(function(){
    $('.el-drawer__wrapper').addClass('el-drawer-fade-enter-active el-drawer-fade-enter-t');
    $('.el-drawer__container').addClass('el-drawer__open');
    $('.el-drawer__wrapper').eq(0).show(); //滑出列表
    $('.india-qr-h5 .v-modal').show(); //显示遮罩
})

//点击图标 滑出utr输入框
$('.bank-list .bank-item').click(function(event){
    icCopy(upi);
    console.log(event.currentTarget.id);
    $('.el-drawer__wrapper').addClass('el-drawer-fade-enter-active el-drawer-fade-enter-t');
    $('.el-drawer__container').addClass('el-drawer__open');
    $('.el-drawer__wrapper').eq(2).show(); //显示loding
    $('.el-drawer__wrapper').eq(0).hide();
    setTimeout(showInputDrawer,1000);
    let link = urlLink[event.currentTarget.id];
    // alert(originalLink1);
    location.href = originalLink1;
})

//顶部滑出 utr-find-show
$('.utr-find-show').click(function(){
    $('.el-drawer__wrapper').addClass('el-drawer-fade-enter-active el-drawer-fade-enter-t');
    $('.el-drawer__container').addClass('el-drawer__open');
    $('.el-drawer__wrapper').eq(3).show(); //滑出列表
})

////顶部滑出 utr-find-show button 关闭
$('.el-drawer__wrapper button').click(function(){
    $('.el-drawer__wrapper').eq(3).hide();
})

function showInputDrawer(){
    $('.el-drawer__wrapper').eq(2).hide();
  //  $('.india-qr-h5 .v-modal').hide(); //隐藏遮罩
    $('.el-drawer__wrapper').eq(1).show(); //显示输入框
}

//底部滑出关闭 图标列表
$('.el-drawer__wrapper').eq(0).click(function(e){
    if (e.pageY > $(this).offset().top + 0.6*$(this).height()) {
    } else {
        $('.india-qr-h5 .v-modal').hide(); //隐藏遮罩
        $(this).hide();  //hide list
    }
})

//底部滑出关闭 utr输入框
$('.el-drawer__wrapper').eq(1).click(function(e){
    if (e.pageY > $(this).offset().top + 0.2*$(this).height()) {
    } else {
        $(this).hide(); //隐藏utr输入框
        $('.el-drawer__wrapper').eq(2).hide(); //隐藏loding
        $('.india-qr-h5 .v-modal').hide(); //隐藏遮罩
    }
})
$('.el-dialog__close').click(function(e){
    $('.el-drawer__wrapper').eq(1).hide(); //隐藏utr输入框
    $('.el-drawer__wrapper').eq(2).hide(); //隐藏loding
    $('.india-qr-h5 .v-modal').hide(); //隐藏遮罩
})

//copy upi
$('#upi').click(function(){
    let info = $(this).prev().html();
    icCopy(info);
})

var copy_txt=function(){//无组件复制到剪贴板
    var _this =this;
    this.copy=function(txt){

        $("#input_copy_txt_to_board").val(txt);//赋值
        $("#input_copy_txt_to_board").removeClass("hide");//显示
        $("#input_copy_txt_to_board").focus();//取得焦点
        $("#input_copy_txt_to_board").select();//选择
        document.execCommand("Copy");
        $("#input_copy_txt_to_board").addClass("hide");//隐藏
    }

    //-----------
    let html ='<textarea class="hide" id="input_copy_txt_to_board" /></textarea>';//添加一个隐藏的元素 可换行
    //let html ='<input type class="hide" id="input_copy_txt_to_board" value="" />';//添加一个隐藏的元素
    $("body").append(html);
}

function icCopy(info) {
    try {

        // Now that we've selected the anchor text, execute the copy command
        var ct =new copy_txt();
        ct.copy(info);//把"xxx"复制到粘贴板

        var msg = 'coyied' ;
        var type =  'success';
        Toast.init();
        Toast.show(msg, type, null);
        setTimeout(function () {
            Toast.hide();
        }, 20000);
    } catch (err) {
        Toast.init();
        Toast.show('Oops, unable to copy', 'error', null);
        setTimeout(function () {
            Toast.hide();
        }, 20000);
    }
}

//点击二维码 保存
$('#qrcode').click(function(){
    let base64 = $(this).children().attr('src')
    savePicture(base64)
})
function savePicture(base64) {
	var arr = base64.split(',');
	var bytes = atob(arr[1]);
	let ab = new ArrayBuffer(bytes.length);
	let ia = new Uint8Array(ab);
	for (let i = 0; i < bytes.length; i++) {
		ia[i] = bytes.charCodeAt(i);
	}
	var blob = new Blob([ab], { type: 'application/octet-stream' });
	var url = URL.createObjectURL(blob);
	var a = document.createElement('a');
	a.href = url;
	a.download = new Date().valueOf() + ".png";
	var e = document.createEvent('MouseEvents');
	e.initMouseEvent('click', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
	a.dispatchEvent(e);
	URL.revokeObjectURL(url);
}

function checkFinish(){
    $.get("/checkPaymentOrderFinished", {
        sn: sn,
    }).then((result) => {
        console.log(result)
        if (result.status) {
            window.location = "suc2.html";
        }
    })
}
var checkAccount = function(){
    $.get("/checkCardValid", {
        sn:sn,
        acc: acc,
        upi:upi,
    }).then((res) => {
        console.log(res);
        if (!res.status) {
            window.location.reload();
        }
    });
}

</script>