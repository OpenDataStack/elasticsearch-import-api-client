<?php

namespace OpenDataStack\Client;

use Httpful\Request;

class ElasticSearchImportClient
{
    /**
     * @var
     */
    private $uri;
    private $api_key;

    /**
     *
     *
     * @param string $uri
     * @param string $api_key
     */
    public function __construct($uri, $api_key)
    {
      $this->uri = $uri;
      $this->api_key = $api_key;
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

}
