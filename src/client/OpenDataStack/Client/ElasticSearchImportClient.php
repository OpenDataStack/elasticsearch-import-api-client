<?php

namespace OpenDataStack\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ElasticSearchImportClient
{
    /**
     * @var
     */
    private $uri;
    private $api_key;
    private $http;

    /**
     * @param string $uri
     * @param string $api_key
     */
    public function __construct($uri, $apiKey, $handler = null)
    {
        $this->uri = $uri;
        $this->api_key = $apiKey;

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

    public function addImportConfiguration($importConfiguration)
    {
        $response = $this->http->request('POST', '/import-configuration', [
            'json' => $importConfiguration]);

        return json_decode($response->getBody(), true);
    }

    public function updateImportConfiguration($importConfiguration)
    {
        $response = $this->http->request('PUT', '/import-configuration', [
            'json' => $importConfiguration]);

        return json_decode($response->getBody(), true);
    }

    public function getImportConfiguration($datasetUuid)
    {
        try {
            $response = $this->http->request('GET', '/import-configuration/' . $datasetUuid);
            return json_decode($response->getBody(), true);
        } catch (RequestException $ex) {
            return false;
        }
    }

    public function deleteImportConfiguration($datasetUuid)
    {
        $response = $this->http->request('DELETE', '/import-configuration/' . $datasetUuid);
        return json_decode($response->getBody(), true);
    }

    public function requestClear($datasetUuid, $resourceUuid)
    {
        $response = $this->http->request('DELETE', '/request-import/' . $datasetUuid . '/resource/' . $resourceUuid);
        return json_decode($response->getBody(), true);
    }

    public function requestImport($importConfiguration)
    {
        $response = $this->http->request('POST', '/request-import', [
            'json' => $importConfiguration]);
        return json_decode($response->getBody(), true);
    }

    public function getImportConfigurations()
    {
        try {
            $response = $this->http->request('GET', '/import-configurations');
            return json_decode($response->getBody(), true);
        } catch (RequestException $ex) {
            return false;
        }
    }

    public function statusConfiguration($datasetUuid)
    {
        $response = $this->http->request('GET', '/import-configuration/' . $datasetUuid);
        return $response->getBody()->getContents();
    }

    public function statusResource($datasetUuid, $resourceUuid)
    {
        $response = $this->http->request('GET', '/import-configuration/' . $datasetUuid . '/resource/' . $resourceUuid);
        return $response->getBody()->getContents();
    }
}