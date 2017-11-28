<?php

namespace OpenDataStack\Client;

use GuzzleHttp\Client;


class ElasticSearchImportClient
{
    /**
     * @var
     */
    private $uri;
    private $api_key;
    private $http;

    /**
     *
     *
     * @param string $uri
     * @param string $api_key
     */
    public function __construct($uri, $api_key, $handler = null)
    {
        $this->uri = $uri;
        $this->api_key = $api_key;

        $config = [
            // Base URI is used with relative requests
            'base_uri' => $this->uri,
            // You can set any number of default request options.
            'timeout' => 2.0,
        ];
        if ($handler) {
            $config['handler'] = $handler;
        }

        $this->http = new Client($config);

    }

    /**
     * @param string $uid
     */
    public function queue($uid)
    {
        $response = Request::post($this->uri)
            ->send();
        print $response->body;
        return "Success!";
    }

    public function addImportConfiguration($importConfiguration)
    {
        $response = $this->http->request('GET', '/import-configuration');
        return json_decode($response->getBody(), true);
    }

}
