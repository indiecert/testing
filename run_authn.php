#!/usr/bin/php
<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Url;

class IndieCertTest
{
    private $instanceUrl;
    private $me;

    public function __construct($instanceUrl, $me)
    {
        $this->instanceUrl = $instanceUrl;
        $this->me = $me;
    }

    public function runAuthentication()
    {
        $generatedState = bin2hex(openssl_random_pseudo_bytes(8));

        $authParams = array(
            'me' => $this->me,
            'response_type' => 'code',
            'client_id' => 'https://example.org/',
            'redirect_uri' => 'https://example.org/callback',
            'state' => $generatedState,
        );

        $authUri = sprintf(
            '%s/auth?%s',
            $this->instanceUrl,
            http_build_query($authParams)
        );
        $confirmUri = sprintf(
            '%s/confirm?%s',
            $this->instanceUrl,
            http_build_query($authParams)
        );

        $client = new Client(
            array(
                'defaults' => array(
                    'verify' => false,
                ),
            )
        );

        // AUTH
        $response = $client->get(
            $authUri,
            array(
                'cert' => __DIR__.'/client.crt',
                'ssl_key' => __DIR__.'/client.key',
            )
        );

        // CONFIRM
        $response = $client->post(
            $confirmUri,
            array(
                'cert' => __DIR__.'/client.crt',
                'ssl_key' => __DIR__.'/client.key',
                'headers' => array(
                    'Referer' => $this->instanceUrl.'/',
                ),
                'allow_redirects' => false,
            )
        );

        $u = Url::fromString($response->getHeader('Location'));
        $q = $u->getQuery();
        $code = $q['code'];
        $state = $q['state'];

        if ($state !== $generatedState) {
            throw new Exception('non matching state');
        }

        $verifyUri = sprintf(
            '%s/auth',
            $this->instanceUrl
        );

        // VALIDATE
        $response = $client->post(
            $verifyUri,
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                ),
                'body' => array(
                    'code' => $code,
                    'state' => $generatedState,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => 'https://example.org/callback',
                    'client_id' => 'https://example.org/',
                ),
            )
        );

        $responseData = $response->json();
        if ($responseData['me'] !== $this->me) {
            throw new Exception('non matching me');
        }

        echo 'DONE'.PHP_EOL;
    }
}

// <link rel="publickey" href="ni:///sha-256;PyZcDB-vGYhFswBBa1kT7wHyctDFKvdvYLrcKTftVg8?ct=application/x-x509-user-cert">
try {
    $i = new IndieCertTest('https://indiecert.example.org', 'https://fkooman.ursa.uberspace.de/');
    $i->runAuthentication();
} catch (Exception $e) {
    echo $e->getMessage();
    #echo $e->getRequest();
    #echo $e->getResponse()->getBody();
}
