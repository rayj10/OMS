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

if($code) {
    print "Successful login...";

    #variables to register
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $institution = $_POST['institution'];

    if ($_POST['form'] == "register"){
        addUserIntranet($username, $name, $email, $phone, $institution);
        radiusAPI(xmlrpc_encode_request("ADD",array($username, $password)));
    }
    else if ($_POST['form'] == "addRadius") {
        radiusAPI(xmlrpc_encode_request("ADD",array($username, $password)));
    }
    else if ($_POST['form'] == "addIntranet") {
        addUserIntranet($username, $name, $email, $phone, $institution);
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
