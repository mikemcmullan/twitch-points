<?php

namespace App\Support;

use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CallApi
{
    public function __construct()
    {

    }

    protected function callApi($url)
    {
        return Cache::remember('api:' . md5($url), 1, function () use ($url) {
            $domain = makeDomain(config('app.api_domain'));

            try {
                $http = new Client();
                $response = $http->request('GET', $domain . '/' . ltrim($url, '/'));

                return (string) $response->getBody();
            } catch (ClientException $e) {
                return null; //(string) $e->getResponse()->getBody();
            }
        });
    }

    public function currencyScoreboard($channel, $page = 1)
    {
        $url = sprintf('/%s/currency?page=%d', $channel, $page);

        return $this->callApi($url);
    }

    public function viewer($channel, $handle)
    {
        $url = sprintf('/%s/viewer?handle=%s', $channel, $handle);

        return $this->callApi($url);
    }
}
