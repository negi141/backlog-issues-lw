<?php
require __DIR__ . '/vendor/autoload.php'; // path to vendor/


$request_body = json_decode('{
  "personalizations": [
    {
      "to": [
        {
          "email": "'.getenv("MAIL_TO").'"
        }
      ],
      "subject": "Hello World from the SendGrid PHP Library!"
    }
  ],
  "from": {
    "email": "'.getenv("MAIL_FROM").'"
  },
  "content": [
    {
      "type": "text/plain",
      "value": "Hello, Email!"
    }
  ]
}');

$apiKey = getenv('SENDGRID_API_KEY');
$sg = new \SendGrid($apiKey);

$response = $sg->client->mail()->send()->post($request_body);
echo $response->statusCode();
echo $response->body();
echo $response->headers();