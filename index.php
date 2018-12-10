<?php    

include 'functions.php';

if(session_id() == '') {
   session_start();
}

$session_namespace = "jakarta";

$result = $_POST['result'];
$mac = $_GET['mac'];
$mac = preg_replace("/\:/","",$mac);
$mac = strtolower($mac);

if(isset($_POST['reset_state']) && !empty($_POST['reset_state'])) {
        unset($_SESSION[$session_namespace.'.state']);
        $current_state = '';
}

if( !empty($_SESSION[$session_namespace.'.state']) ) {
        $current_state = $_SESSION[$session_namespace.'.state'];
} elseif(isset($_POST['state'])) {
        $current_state = strip_tags($_POST['state']);
        $current_state = ($current_state == 'login-free' || $current_state == 'login-premium') ? $current_state : '';
        $_SESSION[$session_namespace.'.state'] = $current_state;
} else {
        $current_state = '';
}

if( empty($_SESSION[$session_namespace.'.link_login_only']) && empty($_SESSION[$session_namespace.'.gateway']) ) {
        //echo 'Write REQUEST parameter data to session<br />';
        $_SESSION[$session_namespace.'.link_login_only']        = (!empty($_REQUEST['linkloginonly'])) ? strip_tags($_REQUEST['linkloginonly']) : '';
        $_SESSION[$session_namespace.'.link_login']             = (!empty($_REQUEST['link-login'])) ? strip_tags($_REQUEST['link-login']) : '';
        $_SESSION[$session_namespace.'.gateway']                        = (!empty($_REQUEST['gateway'])) ? strip_tags($_REQUEST['gateway']) : '';
        $_SESSION[$session_namespace.'.location']                       = (!empty($_REQUEST['lok'])) ? strip_tags($_REQUEST['lok']) : '';
        $_SESSION[$session_namespace.'.desired_url']            = (!empty($_REQUEST['link-orig'])) ? strip_tags($_REQUEST['link-orig']) : '';
        $_SESSION[$session_namespace.'.error_message']          = (!empty($_REQUEST['error'])) ? strip_tags($_REQUEST['error']) : '';
} elseif(!empty($_GET['linkloginonly']) && !empty($_GET['gateway'])) {
        //echo 'Write POST parameter data to session<br />';
        $_SESSION[$session_namespace.'.link_login_only']        = (!empty($_GET['linkloginonly'])) ? strip_tags($_GET['linkloginonly']) : '';
        $_SESSION[$session_namespace.'.link_login']             = (!empty($_GET['link-login'])) ? strip_tags($_GET['link-login']) : '';
        $_SESSION[$session_namespace.'.gateway']                        = (!empty($_GET['gateway'])) ? strip_tags($_GET['gateway']) : '';
        $_SESSION[$session_namespace.'.location']                       = (!empty($_GET['lok'])) ? strip_tags($_GET['lok']) : '';      
        $_SESSION[$session_namespace.'.desired_url']            = (!empty($_GET['link-orig'])) ? strip_tags($_GET['link-orig']) : '';
        $_SESSION[$session_namespace.'.error_message']          = (!empty($_GET['error'])) ? strip_tags($_GET['error']) : '';
}

$error_message = '';
if(!empty($_REQUEST['error'])) {
        $error_message = $_REQUEST['error'];
} elseif(!empty($_SESSION[$session_namespace.'.error_message'])) {
        $error_message = $_SESSION[$session_namespace.'.error_message'];
        //$_SESSION[$session_namespace.'.error_message'] = '';
}


$link_login_only = $_SESSION[$session_namespace.'.link_login_only'];
$link_login = $_SESSION[$session_namespace.'.link_login'];
$gateway = $_SESSION[$session_namespace.'.gateway'];
$location = $_SESSION[$session_namespace.'.location'];
$desired_url = $_SESSION[$session_namespace.'.desired_url'];

/** Mock Data */
#$mac= "000a959d6816";   //registered on radius & intranet
#$mac="9932423423";     //registered on intranet
#print "<pre>". print_r($radiusData,true) ."</pre>"; 

$form = 'register';     //default page in case mac doesn't exist
if($mac) {
        $radiusData = preg_split("/\,/", radiusAPI(xmlrpc_encode_request("CHK",$mac)));
	$radiusData = $radiusData[0];
        $intranetData = checkMacIntranet($mac);
 
        #If mac exists on Intranet & Radius, do nothing to data on both ends
        if ($intranetData && $radiusData) {  
                $form = 'login';
                $name = $intranetData->nama;
        }
        #If mac exists on Intranet but not Radius, add intranet mac to radius
        else if ($intranetData && !$radiusData) {
                $form = 'addRadius';
                $name = $intranetData->nama;
        }
        #If mac exists on Radius but not Intranet, register user to intranet
        else if (!$intranetData && $radiusData) {
                $form = 'addIntranet';
        }
        #If mac doesn't exist on both, register to both
        else {
                $form = 'register';
        }
}

if ($form == 'login' || $form == 'addRadius'){
        print <<<HTML
        <html>
        <head> 
                <title>QR Code Input</title>
                <link href="https://fonts.googleapis.com/css?family=Roboto:400,700i,700" rel="stylesheet">
                <link rel="stylesheet" type="text/css" href="./styles/index.css">
                <script type="text/javascript" src="./js/grid.js"></script>
                <script type="text/javascript" src="./js/version.js"></script>
                <script type="text/javascript" src="./js/detector.js"></script>
                <script type="text/javascript" src="./js/formatinf.js"></script>
                <script type="text/javascript" src="./js/errorlevel.js"></script>
                <script type="text/javascript" src="./js/bitmat.js"></script>
                <script type="text/javascript" src="./js/datablock.js"></script>
                <script type="text/javascript" src="./js/bmparser.js"></script>
                <script type="text/javascript" src="./js/datamask.js"></script>
                <script type="text/javascript" src="./js/rsdecoder.js"></script>
                <script type="text/javascript" src="./js/gf256poly.js"></script>
                <script type="text/javascript" src="./js/gf256.js"></script>
                <script type="text/javascript" src="./js/decoder.js"></script>
                <script type="text/javascript" src="./js/qrcode.js"></script>
                <script type="text/javascript" src="./js/findpat.js"></script>
                <script type="text/javascript" src="./js/alignpat.js"></script>
                <script type="text/javascript" src="./js/databr.js"></script>
                <script type="text/javascript" src='./js/modernizr.js'> </script>
                <script type="text/javascript" src="./js/qrdecoder.js"></script>
                <script type="text/javascript" src='./js/validate.js'> </script>
                <script type="text/javascript">
                        function detectBrowser() {
                                var isFirefox = typeof InstallTrigger !== 'undefined';
                                var isChrome = !!window.chrome && !!window.chrome.webstore;
                                var isBrowser = Modernizr.pagevisibility && Modernizr.sessionstorage && Modernizr.localstorage && Modernizr.hashchange;
                                var getUserMedia = (navigator.getUserMedia ||
                                                navigator.webkitGetUserMedia ||
                                                navigator.mozGetUserMedia || navigator.mediaDevices);

                                if (isChrome || isFirefox) {    //from supported browsers
                                        document.getElementById('code').placeholder = "Scan or enter login code";
                                }
                                else { //maybe from captive
                                        if (navigator.userAgent.match(/ipad|ipod|iphone|macintosh/i) && !getUserMedia) { //apple CNA doesn't have getUserMedia
                                                document.getElementById('qrbutton').style.display = "none";
                                        }
                                        else if (isBrowser) { //in case it is still browser, i.e. Safari but certainly not apple CNA
                                                document.getElementById('code').placeholder = "Scan or enter login code";
                                        }
                                        else {  //definitely not supported browser, could be android CNA
                                                document.getElementById('qrbutton').style.display = "none";
                                        }
                                }
                        }
                </script>
	        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1" /> 
        </head>
        <body onload="detectBrowser();">
        <div id="center">
                <img src="./img/logoCBN.png">
                <h1>
                        Hello $name!
                        <p id="subheader">Please enter your login code</p>
                </h1>
                <form action="goto.php" method="post">
                        <input type="hidden" name="form" value="$form"/>
                        <input type="hidden" name="username" value="$mac"/>
                        <input type="hidden" name="password" value="$mac"/>
                        <input id="code" type=text size=20 name="qrcode" placeholder="Enter login code" class=qrcode-text maxlength=4 />
                        <label id="qrbutton" class=qrcode-text-btn onclick="showScanner();"> </label> 
                        <p id="qrerror" class="error"> $result </p>
                        <p id="errormessage" class="error"> $error_message </p>
                        <video id="video-preview" width=80% style="display: none; margin:auto" ></video>
                        <canvas id="qr-canvas" class="hidden" style="display: none"></canvas>
                        <input id="submit" type="submit" value="Login"/>
                        <!--Additional data-->
			<input type="hidden" name="linkloginonly" value="$link_login_only" />
                        <input type="hidden" name="link-login" value="$link_login" />
                        <input type="hidden" name="link-orig" value="$desired_url" />
                        <input type="hidden" name="gateway" value="$gateway" />
                        <input type="hidden" name="lok" value="$location" />
                        <input type="hidden" name="dst" value="$desired_url"/>
                        <input type="hidden" name="popup" value="false" />
                </form>
        </div>
        </body>
        </html>
HTML;
}
else if ($form == 'addIntranet' || $form == 'register'){
        print <<<HTML
        <html>
        <head> 
                <title>Registration</title>
                <link href="https://fonts.googleapis.com/css?family=Roboto:400,700i,700" rel="stylesheet">
                <link rel="stylesheet" type="text/css" href="./styles/index.css">
                <script type="text/javascript" src="./js/grid.js"></script>
                <script type="text/javascript" src="./js/version.js"></script>
                <script type="text/javascript" src="./js/detector.js"></script>
                <script type="text/javascript" src="./js/formatinf.js"></script>
                <script type="text/javascript" src="./js/errorlevel.js"></script>
                <script type="text/javascript" src="./js/bitmat.js"></script>
                <script type="text/javascript" src="./js/datablock.js"></script>
                <script type="text/javascript" src="./js/bmparser.js"></script>
                <script type="text/javascript" src="./js/datamask.js"></script>
                <script type="text/javascript" src="./js/rsdecoder.js"></script>
                <script type="text/javascript" src="./js/gf256poly.js"></script>
                <script type="text/javascript" src="./js/gf256.js"></script>
                <script type="text/javascript" src="./js/decoder.js"></script>
                <script type="text/javascript" src="./js/qrcode.js"></script>
                <script type="text/javascript" src="./js/findpat.js"></script>
                <script type="text/javascript" src="./js/alignpat.js"></script>
                <script type="text/javascript" src="./js/databr.js"></script>
                <script type="text/javascript" src='./js/modernizr.js'> </script>
                <script type="text/javascript" src="./js/qrdecoder.js"></script>
                <script type="text/javascript" src='./js/validate.js'> </script>
                <script type="text/javascript">
                       function detectBrowser() {
                                var isFirefox = typeof InstallTrigger !== 'undefined';
                                var isChrome = !!window.chrome && !!window.chrome.webstore;
                                var isBrowser = Modernizr.pagevisibility && Modernizr.sessionstorage && Modernizr.localstorage && Modernizr.hashchange;
                                var getUserMedia = (navigator.getUserMedia ||
                                                navigator.webkitGetUserMedia ||
                                                navigator.mozGetUserMedia || navigator.mediaDevices);

                                if (isChrome || isFirefox) {    //from supported browsers
                                        document.getElementById('code').placeholder = "Scan or enter login code";
                                }
                                else { //maybe from captive
                                        if (navigator.userAgent.match(/ipad|ipod|iphone|macintosh/i) && !getUserMedia) { //apple CNA doesn't have getUserMedia
                                                document.getElementById('qrbutton').style.display = "none";
                                        }
                                        else if (isBrowser) { //in case it is still browser, i.e. Safari but certainly not apple CNA
                                                document.getElementById('code').placeholder = "Scan or enter login code";
                                        }
                                        else {  //definitely not supported browser, could be android CNA
                                                document.getElementById('qrbutton').style.display = "none";
                                        }
                                }
                        }
                </script>
	        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1" /> 
        </head>
        <body onload="detectBrowser();">
        <div id="center">
                <img src="./img/logoCBN.png">
                <h1>
                        Hello!
                        <p id="subheader">Please enter your details below</p>
                </h1>
                <form  action="goto.php" method="post" name="regForm" onsubmit="return validateForm(this);">
                        <input type="hidden" name="form" value="$form"/>
                        <input type="hidden" name="username" value="$mac"/>
                        <input type="hidden" name="password" value="$mac"/>
                        <p id="validation" class="error" style="display:none"> </p>
                        <input name="name" placeholder="Full Name" class=qrcode-text /><br/>
                        <input name="email" placeholder="Email Adress" class=qrcode-text /><br/>
                        <input name="phone" placeholder="Phone Number" class=qrcode-text /><br/>
                        <input name="institution" placeholder="School/Workplace Name" class=qrcode-text /><br/>
                        <br/>
                        <input id="code" type=text size=20 name="qrcode" placeholder="Enter login code" class=qrcode-text maxLength=4/>
                        <label id="qrbutton" class=qrcode-text-btn onclick="showScanner();"> </label> 
                        <p id="qrerror" class="error"> $result </p>
                        <p id="errormessage" class="error"> $error_message </p>
                        <video id="video-preview" width=80% style="display: none; margin:auto" ></video>
                        <canvas id="qr-canvas" class="hidden" style="display: none"></canvas>
                        <input id="submit" type="submit" value="Register"/>
                        <!--Additional data-->
			<input type="hidden" name="linkloginonly" value="$link_login_only" />
                        <input type="hidden" name="link-login" value="$link_login" />
                        <input type="hidden" name="link-orig" value="$desired_url" />
                        <input type="hidden" name="gateway" value="$gateway" />
                        <input type="hidden" name="lok" value="$location" />
                        <input type="hidden" name="dst" value="$desired_url"/>
                        <input type="hidden" name="popup" value="false" />
                </form>
        </div>
        </body>
        </html>
HTML;
}

?>    
