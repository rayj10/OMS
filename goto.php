<?php 

include 'functions.php';

session_start();

$qrcode = $_POST['qrcode'];
if(strlen($qrcode) == 4) { //injection prevention
    $rpm = do_call_api('Qrcode_list',array('code'=>$qrcode));
    $code = $rpm[1];
}
else{
    $code = null;
}

$phone = $_POST['phone'];
$phone = htmlspecialchars($phone); //cross-site-scripting prevention
if(strlen($phone) > 14) {       //injection prevention
    $phone = null;
}

if($code) {
    print "Successful login...";

    #variables to register
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($_POST['form'] == "addRadius") {
        radiusAPI(xmlrpc_encode_request("ADD",array($username, $password)));
    }

    radiusAPI(xmlrpc_encode_request("UPDATE_EXPIREDATE",$username)); # untuk update expiredate tambah 1 hari
    
    $do = $_POST['do'];
    $linkloginonly = $_POST['linkloginonly'];
    $link_login = $_POST['link-login'];
    $gateway = $_POST['gateway'];
    $lok = $_POST['lok'];
    $link_orig = $_POST['link-orig'];
    $welcome = "http://10.64.2.61/oms/welcome_page.php?link_logout=$(link-logout)";
	
print <<<HTML
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Redirecting</title>
    </head>
    <body onload="document.getElementById('frm_redirect').submit();">
        <form id="frm_redirect" name="frm_redirect" action="$linkloginonly" method="post"> 
            <input type="hidden" name="username" value="$username" />
            <input type="hidden" name="password" value="$password" />
	    <input type="hidden" name="dst" value="$welcome" />
        </form>
    </body>
</html>
HTML;
} 
else if ($_POST['form'] == "checkPhone" && $phone){
    $exists = checkPhoneGuestBook($phone);
    
    if ($exists){
print <<<HTML
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Redirecting</title>
    </head>
        <body onload="document.getElementById('frm_redirect').submit();">
                <form id="frm_redirect" name="frm_redirect" action="index.php" method="post">
                    <input type="hidden" name="phone" value="$phone" />
                </form>
        </body>
</html>
HTML;
    }
    else {
print <<<HTML
<html lang="en">
    <head> 
        <title> Oops! </title>
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700i,700" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://oms.cbn.net.id/styles/welcome.css">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1" />
    </head>
    <body>
        <div id="center">
            <h1>Oops!</h1><hr/> 
            <p id="info">Looks like you have not signed our GuestBook yet</p>
            <p id="info">Please do this at our reception desk to get connected to our Wifi</p>
        </div>
    </body>
</html>
HTML;
    }
}
else {
print <<<HTML
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Redirecting</title>
    </head>
        <body onload="document.getElementById('frm_redirect').submit();">
                <form id="frm_redirect" name="frm_redirect" action="index.php" method="post">
                        <input type="hidden" name="result" value='"$qrcode" is not a valid login code' />
                </form>
        </body>
</html>
HTML;
}
?>
