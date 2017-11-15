<?php

class BacklogApi
{
    const API_URL = 'https://%s.backlog.jp/api/v2/%s?%s';
    const WEB_URL = 'https://%s.backlog.jp/view/%s';

    private $space;
    private $apiKey;

    public function __construct($apiKey, $space)
    {
        $this->space = $space;
        $this->apiKey = $apiKey;
    }

    public function send($uri, $query)
    {
        $context = [
            'http' => [
                'method' => 'GET',
                'header' => '',
                'ignore_errors' => true,
            ]
        ];
        $query += ['apiKey' => $this->apiKey];

        $url = sprintf(self::API_URL, $this->space, $uri, http_build_query($query, '', '&'));

        $response = file_get_contents($url, false, stream_context_create($context));
        $data = json_decode($response, true);

        if (isset($data["errors"])) {
            throw new \Exception($data["errors"][0]["message"]);
        }

        return $data;
    }
}