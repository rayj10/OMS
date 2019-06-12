<?php

include('functions.php');

//$rpm = do_call_api('Qrcode_list',array('code'=>'52VN'));

#$rpm = addUserIntranet('1231231','Dodo','dodo@gmail.com','082312312','CBN');
#$rpm = radiusAPI(xmlrpc_encode_request("UPDATE_EXPIREDATE","6c71d978e364"));
$rpm = radiusAPI(xmlrpc_encode_request("CHK","6c71d978e364"));

print_r($rpm);

?>
