<?php
require_once 'config.php';

use GuzzleHttp\Client;

class AmazonAPI
{
    private $host = "webservices.amazon.com.br";
    private $uriPath = "/paapi5/getitems";
    private $region = 'us-east-1';
    private $service = 'ProductAdvertisingAPI';

    public function getBookData($asin)
    {
        $payload = json_encode([
            'ItemIds' => [$asin],
            'PartnerTag' => AWS_ASSOCIATE_TAG,
            'PartnerType' => 'Associates',
            'Marketplace' => 'www.amazon.com.br',
            'Resources' => ['Images.Primary.Large', 'ItemInfo.Title'],
            'LanguagesOfPreference' => ['pt_BR']
        ]);

        $datetime = gmdate('Ymd\THis\Z');
        $headers = $this->getHeaders($datetime);

        $signature = $this->getSignature('POST', $this->uriPath, $payload, $headers, $datetime);
        $headers['Authorization'] = $signature;

        $client = new Client();
        try {
            $response = $client->post("https://{$this->host}{$this->uriPath}", [
                'headers' => $headers,
                'body' => $payload
            ]);

            $data = json_decode($response->getBody(), true);

            // Extrair informações relevantes da resposta
            $item = $data['ItemsResult']['Items'][0];
            $title = $item['ItemInfo']['Title']['DisplayValue'];
            $imageUrl = $item['Images']['Primary']['Large']['URL'];

            return [
                'title' => $title,
                'image_url' => $imageUrl
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            throw new Exception("Erro na API da Amazon: " . $responseBodyAsString);
        }
    }

    private function getHeaders($datetime)
    {
        return [
            'content-encoding' => 'amz-1.0',
            'content-type' => 'application/json; charset=utf-8',
            'host' => $this->host,
            'x-amz-date' => $datetime,
            'x-amz-target' => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems'
        ];
    }

    private function getSignature($method, $uriPath, $payload, $headers, $datetime)
    {
        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = gmdate('Ymd', strtotime($datetime)) . "/{$this->region}/{$this->service}/aws4_request";

        $canonicalHeaders = '';
        $signedHeaders = '';
        ksort($headers);
        foreach ($headers as $key => $value) {
            $canonicalHeaders .= strtolower($key) . ':' . trim($value) . "\n";
            $signedHeaders .= strtolower($key) . ';';
        }
        $signedHeaders = rtrim($signedHeaders, ';');

        $canonicalRequest = $method . "\n"
            . $uriPath . "\n"
            . "\n"
            . $canonicalHeaders . "\n"
            . $signedHeaders . "\n"
            . hash('sha256', $payload);

        $stringToSign = $algorithm . "\n"
            . $datetime . "\n"
            . $credentialScope . "\n"
            . hash('sha256', $canonicalRequest);

        $signingKey = $this->getSigningKey(AWS_SECRET_ACCESS_KEY, gmdate('Ymd', strtotime($datetime)));
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return $algorithm . ' '
            . 'Credential=' . AWS_ACCESS_KEY_ID . '/' . $credentialScope . ', '
            . 'SignedHeaders=' . $signedHeaders . ', '
            . 'Signature=' . $signature;
    }

    private function getSigningKey($key, $date)
    {
        $dateKey = hash_hmac('sha256', $date, 'AWS4' . $key, true);
        $regionKey = hash_hmac('sha256', $this->region, $dateKey, true);
        $serviceKey = hash_hmac('sha256', $this->service, $regionKey, true);
        return hash_hmac('sha256', 'aws4_request', $serviceKey, true);
    }

    public function generateShortAffiliateUrl($asin)
    {
        $longUrl = "https://www.amazon.com.br/dp/{$asin}?tag=" . AWS_ASSOCIATE_TAG;
        return $this->shortenUrl($longUrl);
    }

    private function shortenUrl($longUrl)
    {
        $client = new Client();

        try {
            $response = $client->post('https://api-ssl.bitly.com/v4/shorten', [
                'headers' => [
                    'Authorization' => 'Bearer ' . BITLY_KEY,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'group_guid' => BITLY_GROUP,
                    'long_url' => $longUrl,
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            return $result['link'];
        } catch (\Exception $e) {
            // Se ocorrer um erro, retorna a URL longa original
            error_log("Erro ao encurtar URL: " . $e->getMessage());
            return $longUrl;
        }
    }
}

