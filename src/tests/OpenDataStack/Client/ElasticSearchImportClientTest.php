<?php

namespace OpenDataStack\Client;

use PHPUnit\Framework\TestCase;
use OpenDataStack\Client\ElasticSearchImportClient;

/**
 * @group functional
 */
class ElasticSearchImportClientTest extends TestCase
{

    public function setUp()
    {
        print "hello world setup";
    }

    /**
     *
     * @param mixed $config
     */
    public function testQueueAdd()
    {
        $client = new ElasticSearchImportClient("http://192.168.99.100:8088/ping", "283y2daksjn");
        $response = $client->queue('asoiduh29d');
        $this->assertSame('Success!', $response);
    }

}
