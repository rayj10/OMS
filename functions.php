<?php  

function radiusAPI($request) {
    $context = stream_context_create(array('http' => array(
       'method' => "POST",
       'header' => "Content-Type: text/xml\r\nUser-Agent: PHPRPC/1.0\r\n",
       'content' => $request
    )));
    
    //URL of the XMLRPC Server
    $server = 'http://10.64.2.96/GUESS/xml-rpc-client.pl';
    $file = file_get_contents($server, false, $context);
    $response = xmlrpc_decode($file);
    
    return $response;
}

function checkMacIntranet($mac){
    $request = "http://10.64.2.54/api-mob/api.php?method=User_wifi_list&mac=".$mac."&key=xkRKJui9acBcx4CG/UAdasjajH==";
    $data = file_get_contents($request);
    $json_file = json_decode($data);
    $key= $json_file->Detail->key;
    return $json_file->Detail->data ? $json_file->Detail->data->$key : null;
    //return $json_file;
}

function addUserIntranet($mac, $name, $email, $phone, $institution){
        $url = "http://10.64.2.54/api-mob/api.php?method=User_wifi_add";

        $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query(
                        array(
                            'mac' => $mac,
                            'nama' => $name,
                            'email' => $email,
                            'handphone' => $phone,
                            'instansi' => $institution,
                            'key' => 'xkRKJui9acBcx4CG/UAdasjajH=='
                        )
                    )
                )
            ));
            
            $data = file_get_contents($url, false, $context);
            $json_file = json_decode($data);
            
            return $json_file->Detail->Status;
	    //return $json_file;
}

function do_call_api($fungsi,$arg) {

    $request = xmlrpc_encode_request($fungsi,$arg);
#print "$request";
   $server = 'http://10.64.2.54/RPC2/xmlrpc_intranetdb4.php';
   $header[] = "Content-type: text/xml";
   $header[] = "Content-length: ".strlen($request);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $server);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $response = curl_exec($ch);
    $response = preg_replace("/^\n/",'',$response);
    $response = preg_replace("/^\n/",'',$response);
    $response = preg_replace("/^\n/",'',$response);
#print "$response";
    $data = xmlrpc_decode($response);
    if (curl_errno($ch)) {
        print curl_error($ch);
    } else {
        curl_close($ch);
        return $data;
    }
}

?>    
