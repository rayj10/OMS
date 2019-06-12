<?php    

    $link_logout = $_GET['link_logout'];
	print <<<HTML
<html>
<head> 
    <title> WELCOME </title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700i,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://oms.cbn.net.id/styles/welcome.css">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1" />
</head>
<body>
    <div id="center">
        <h1>WELCOME!</h1><hr/> 
        <p id="info">You are now logged in to CBN Wifi Network</p>
        <form action="$link_logout" name="logout" onSubmit="return openLogout()">
                <input id="submit" type="submit" value="Log Off">
        </form>
    </div>
    <script>
      function openLogout() {
         if (window.name != 'hotspot_status') return true;
         
         open('$link_logout', 'hotspot_logout', 'toolbar=0,location=0,directories=0,status=0,menubars=0,resizable=1,width=280,height=250');
         window.close();
         return false;
      }
   </script>
</body>
</html>
HTML;
    

?>    
