<?php
if (!function_exists('getallheaders')) {
    function getallheaders() {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace('_', ' ', substr($name, 5));
                $name = str_replace(' ', '-', $name);
                $headers[$name] = $value;
            } else if ($name == "CONTENT_TYPE") {
                $headers["Content-Type"] = $value;
            } else if ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"] = $value;
            }
        }
        return $headers;
    }
}

function passthruHg($port = 8001) {
    $curl = curl_init();
    $url = 'http://localhost:' . intval($port) . $_SERVER['REQUEST_URI'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $headers = getallheaders();

    foreach($headers as $key => $header) {
        $headers[$key] = "{$key}: {$header}";
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_ENCODING, $_SERVER['HTTP_ACCEPT_ENCODING']);
    curl_setopt($curl, CURLOPT_AUTOREFERER,    TRUE);
    curl_setopt($curl, CURLOPT_HEADER,         TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = file_get_contents('php://input');

        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    $response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    curl_close($curl);


    $body = substr($response, $header_size);
    $headers = explode("\r\n", trim(substr($response, 0, $header_size), "\r\n"));
    foreach($headers as $header) {
        header($header);
    }

    echo $body;
}

if($_SERVER['HTTP_ACCEPT'] == 'application/mercurial-0.1') {
    passthruHg();
} else {
    echo "Hi!";

}

exit;

/*
    error_log(print_r($_GET, true));
[HTTP_ACCEPT] => application/mercurial-0.1
    $postdata = file_get_contents("php://input");
//  error_log($postdata);
*/
?>
