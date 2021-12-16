

<!------ Include the above in your HEAD tag ---------->

<!-- Selim Doyranlı Tarafından Hazırlanmıştır : 08.10.2016 | Material Form -->
<!-- https://selimdoyranli.com -->
<!-- http://www.radkod.com -->
<!-- https://www.sanalyer.com -->

<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>{{env('APP_NAME')}}</title>

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    
    <!-- Dışarıdan Çağırılan Dosyalar Font we Materyal İkonlar -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- #Dışarıdan Çağırılan Dosyalar Font we Materyal İkonlar Bitiş -->
    
    
</head>
<body>
<style type="text/css">
    
    body {
    margin: 0;
    padding: 0;
    background:#eee;
    font-family: roboto;
    display:flex; /* You delete it on your web page */
    justify-content:center;/* and this - delete */
}
button:hover,
button:focus {
    text-decoration: none;
    color: #eee;
}
.login-card {
    min-height: 100vh;
    background-image: url('https://selimdoyranli.com/cdn/material-form/img/bg.jpg');
    background-size: cover;
    -moz-background-size: cover;
    -ms-background-size: cover;
    -wenkit-background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    position: relative;
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    box-shadow: 0 1px 6px 0 rgba(0, 0, 0, 0.12), 0 1px 6px 0 rgba(0, 0, 0, 0.12);
    z-index: 2;
    padding: 0;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    font-family: roboto!important;
}
.login-card:after {
    background: linear-gradient(to right, #0051a0, #0069b3, #0080c2, #0097cf, #01aed9);
    /* Login Card Arkaplan Rengi */
    
    background: -webkit-linear-gradient(to right, #0051a0, #0069b3, #0080c2, #0097cf, #01aed9);
    /* Login Card Arkaplan Rengi */
    
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    content: "";
    opacity: 0.8;
    z-index: 3;
}
.login-card > form {
    z-index: 4;
    position: relative;
    padding: 0px 25px;
    width: 100%;
}
.logo-kapsul {
    text-align: center;
    position: relative;
    opacity: 0.8;
}
.logo {
    height: auto;
    padding: 10px 0px;
    padding-bottom: 10px;
}
/* form başlangıç stiller ------------------------------- */

.group {
    position: relative;
    margin-bottom: 45px;
}
.group input {
    font-size: 18px;
    padding: 10px 10px 10px 10px;
    display: block;
    width: 100%;
    border: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    background: none;
    color: #eee;
}
.group input:focus {
    outline: none;
}
/* LABEL ======================================= */

.group label {
    color: rgba(255, 255, 255, 0.5);
    font-size: 18px;
    font-weight: normal;
    position: absolute;
    pointer-events: none;
    left: 5px;
    top: 5px;
    transition: 0.2s ease all;
    -moz-transition: 0.2s ease all;
    -webkit-transition: 0.2s ease all;
}
/* active durum */

.group input:focus ~ label,
input:valid ~ label {
    top: -20px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.7);
}
/* BOTTOM BARS ================================= */

.bar {
    position: relative;
    display: block;
    width: 100%;
}
.bar:before,
.bar:after {
    content: '';
    height: 2px;
    width: 0;
    bottom: 1px;
    position: absolute;
    background: rgba(255, 255, 255, 0.7);
    transition: 0.2s ease all;
    -moz-transition: 0.2s ease all;
    -webkit-transition: 0.2s ease all;
}
.bar:before {
    left: 50%;
}
.bar:after {
    right: 50%;
}
/* active durum bar */

.group input:focus ~ .bar:before,
.group input:focus ~ .bar:after {
    width: 50%;
}
/* HIGHLIGHTER ================================== */

.highlight {
    position: absolute;
    height: 0%;
    width: 100px;
    top: 25%;
    left: 0;
    pointer-events: none;
    opacity: 0.5;
}
/* active durum */

.group input:focus ~ .highlight {
    -webkit-animation: inputHighlighter 0.3s ease;
    -moz-animation: inputHighlighter 0.3s ease;
    animation: inputHighlighter 0.3s ease;
}
/* form animasyon ================ */

@-webkit-keyframes inputHighlighter {
    from {
        background: rgba(255, 255, 255, 0.7);
    }
    to {
        width: 0;
        background: transparent;
    }
}
@-moz-keyframes inputHighlighter {
    from {
        background: rgba(255, 255, 255, 0.7);
    }
    to {
        width: 0;
        background: transparent;
    }
}
@keyframes inputHighlighter {
    from {
        background: rgba(255, 255, 255, 0.7);
    }
    to {
        width: 0;
        background: transparent;
    }
}
.input-ikon {
    font-size: 25px!important;
    position: relative;
}
.input-sifre-ikon {
    font-size: 22px!important;
    position: relative;
}
.span-input {
    margin-left: 10px;
    position: relative;
    top: -5px;
}
.giris-yap-buton,
.kayit-ol-buton,
.sifre-hatirlat-buton {
    background: linear-gradient(to right, #04192c, #0e3552, #1c516c, #134c61, #0c3742);
    background: -webkit-linear-gradient(to right, #04192c, #0e3552, #1c516c, #134c61, #0c3742);
    display: block;
    text-align: center;
    text-decoration: none;
    color: #eee;
    font-family: roboto;
    font-weight: 100;
    padding: 10px;
    border-radius: 3px;
    outline: none;
    opacity: 0.8;
}
.forgot-and-create {
    margin: 20px 0px;
}
.forgot-and-create a {
    color: #bbb;
    font-size: 12px;
    text-decoration: none;
    font-weight: 100;
    margin-right: 10px;
}
/* Geçiş Links Forgot and Create */

.zaten-hesap-var-link {
    color: #bbb;
    font-size: 14px;
    padding: 20px 0px;
    text-decoration: none;
    display: block;
}
</style>

<div class="col-lg-4 col-md-7 col-sm-6 col-xs-12     login-card">

    <form id="login-form" class="col-lg-12" action="" method="post">
        <div class="col-lg-12 logo-kapsul">
            <img width="100" class="logo" src="https://selimdoyranli.com/cdn/material-form/img/logo.png" alt="Logo" />

            <h3 style="font-family: 'Raleway', sans-serif;font-weight: 100;color: white; padding-bottom:10px;" class="text-center head">Please enter your credentials</h3>
        </div>

        <div style="clear:both;"></div>

        <div class="group">
            <input type="hidden" name="token" value="{{$token}}">
            <input type="text" name="email" required>
            <span class="highlight"></span>
            <span class="bar"></span>
            <label><i class="material-icons input-ikon">person_outline</i><span class="span-input">Email</span></label>
        </div>
  
        <div class="group">
            <input type="password"  name="password" required>
            <span class="highlight"></span>
            <span class="bar"></span>
            <label><i class="material-icons input-sifre-ikon">lock</i><span class="span-input">Password</span></label>
        </div>

          <div class="group">
            <input type="password"  name="password_confirmation" required>
            <span class="highlight"></span>
            <span class="bar"></span>
            <label><i class="material-icons input-sifre-ikon">lock</i><span class="span-input">Confirm Password</span></label>
        </div>
        <button  id="passwordResetButton" type="button" style="width: 100%;border: none;" class="giris-yap-buton">Change Password</button>
    </form>
</div>
</body>
<script type="text/javascript" src="{{ asset('js/notify/notify.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/notify/notify.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $("#passwordResetButton").click(function(e){
        var formData = $('#login-form').serialize();
        //alert("{{env('APP_URL')}}/api/auth/reset");
         $.ajax({  
            type: "POST",  
            url: "{{env('APP_URL')}}/api/auth/reset",  
            data:  $('#login-form').serialize(),  
            beforeSend: function() {
               $("#passwordResetButton").html('<i class="fa fa-circle-o-notch fa-spin"></i> Loading');
            },
            success: function(dataString) {  
                $("#passwordResetButton").html('Confirm Password');
                $("#passwordResetButton").attr("disabled","disabled");
                //window.location.href = "{{env('APP_URL')}}";
                $.notify("Success, Password changed !", "success");
            },
            error: function(dataString) { 
                $("#passwordResetButton").html('Confirm Password');
                $.notify("Error in change password, Please try again", "error");
                
            }
        });   
    });

//Kaydol - Şifre Unuttum Linkleri Arası Geçiş
    $(document).ready(function(){
        $("#kayit-form").hide();
        $("#sifre-hatirlat-form").hide(); 

        $(".hesap-olustur-link").click(function(e){
            $("#login-form").slideUp(0);    
            $("#kayit-form").fadeIn(300);   
        });

        $(".zaten-hesap-var-link").click(function(e){
            $("#kayit-form").slideUp(0);
            $("#sifre-hatirlat-form").slideUp(0);   
            $("#login-form").fadeIn(300);   
        });

        $(".sifre-hatirlat-link").click(function(e){
            $("#login-form").slideUp(0);    
            $("#sifre-hatirlat-form").fadeIn(300);  
        });
    });

});
</script>
</html>
