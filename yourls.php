<?php
require_once 'config.php';

require_once 'vendor/autoload.php';
use GuzzleHttp\Client;

function yourls_shorten($url, $title = null) {
    if (!defined('YOURLS_API_URL') || !defined('YOURLS_TOKEN')) {
        throw new Exception('YOURLS_API_URL e YOURLS_TOKEN devem ser definidos no arquivo config.php');
    }
    $client = new Client();
    $params = [
        'query' => [
            'action' => 'shorturl',
            'url' => $url,
            'format' => 'json',
            'signature' => YOURLS_TOKEN
        ]];
    if ($title) {
        $params['query']['title'] = $title;
    }

    $response = $client->get(YOURLS_API_URL . '/yourls-api.php', $params);

    $data = json_decode($response->getBody(), true);
    return $data['shorturl'];
}

function yourls_urls_array($urls, $productTitle = null) {
    $shortUrls = [];
    foreach ($urls as $key => $url) {
        if ($productTitle) {
            $urlTitle = '(' . $key . ') ' . $productTitle;
            $shortUrls[$key] = yourls_shorten($url, $urlTitle);
            continue;
        }
        $shortUrls[$key] = yourls_shorten($url);
    }
    return $shortUrls;
}
