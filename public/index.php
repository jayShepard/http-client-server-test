<?php
require __DIR__ . '/../vendor/autoload.php';

use \pillr\library\http\Response as Response;

# TIP: Use the $_SERVER Sugerglobal to get all the data your need from the Client's HTTP Request.

# TIP: HTTP headers are printed natively in PHP by invoking header().
#      Ex. header('Content-Type', 'text/html');


$uri = $_SERVER['REQUEST_URI'];
$sent = date('D, d M Y H:i:s T');
$body = <<<EOT
<pre>
{
    "@id": $uri,
    "to": 'Pillr',
    "subject": 'Hello Pillr',
    "message": 'Here is my submission',
    "from": "Jay Shepard", 
    "timeSent": $sent
}
</pre>
EOT;

$headers = [
    "Date" => date('D, d M Y H:i:s T'),
    "Server" => $_SERVER["SERVER_NAME"],
    "Last_modified" => date('D, d M Y H:i:s T'),
    "Content-Length" => strlen($body),
    "Content-Type" => "application/json"
];

$response = new Response("1.1", '200', 'OK', $headers, $body);

echo "HTTP/". $response->getProtocolVersion(). " ". $response->getStatusCode(). " ". $response->getReasonPhrase(). "<br>";
echo  $response->getHeaders(). "<br>";
echo $response->getBody();