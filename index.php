<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/pb/GPBMetadata/Helloworld.php';
require_once __DIR__ . '/pb/Helloworld/HelloReply.php';
require_once __DIR__ . '/pb/Helloworld/HelloRequest.php';

$body = file_get_contents('php://input');

$request = new \Helloworld\HelloRequest();
$request->mergeFromString($body);

$response = new \Helloworld\HelloReply();
$response->setMessage(sprintf("Hello %s", $request->getName()));

header('Content-Type: application/grpc');

print($response->serializeToString());
 
