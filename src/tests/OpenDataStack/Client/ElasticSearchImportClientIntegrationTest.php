<?php

namespace OpenDataStack\Tests;

use GuzzleHttp\Exception\ClientException;
use OpenDataStack\Client\ElasticSearchImportClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * @group functional
 */
class ElasticSearchImportClientIntegrationTest extends ElasticSearchImportClientMockTest
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->setClient(new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn"));
    }

    protected function setClient($client)
    {
        $this->client = $client;
    }

    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @group Integrations
     */
    public function testImportConfigurationAdd($client = null)
    {
        parent::testImportConfigurationAdd($this->getClient());
    }

    /**
     * @group Integrations
     */
    public function testImportConfigurationDelete($client = null)
    {
        $client = $this->getClient();
        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        try {
            // Add configuration
            $response = $client->addImportConfiguration($importConfiguration);
            $this->assertArrayHasKey('log', $response);
            $this->assertArrayHasKey('status', $response['log']);
            $this->assertArrayHasKey('message', $response['log']);
            $this->assertEquals($response['log']['status'], 'new');

            // Confirm add worked
            $response = $client->getImportConfiguration($importConfiguration->id);
            $this->assertEquals($importConfiguration->id, $response['id']);

            // Delete configuration
            $response = $client->deleteImportConfiguration($importConfiguration->id);
            $this->assertEquals('200', $response);

            // Confirm delete confirguration has worked
            $response = $client->getImportConfiguration($importConfiguration->id);
            $this->assertEquals(false, $response);
        } catch (ClientException $exception) {
            $this->fail("fail with exception code : {$exception->getCode()} and message : {$exception->getMessage()}");
        }
    }

    /**
     * @group Integrations
     */
    public function testImportRequest()
    {
        $mock = new MockHandler([
            // testImportConfigurationAdd
            new Response(200, [], json_encode(
                [
                    'id' => '22222222-582c-4f29-b1e4-113781e58e3b',
                    'log' => [
                        'status' => 'new',
                        'message' => 'Success'
                    ],
                ]
            )),
            new Response(200, [], json_encode(
                [
                    'id' => '22222222-582c-4f29-b1e4-113781e58e3b',
                    'log' => [
                        'status' => 'queued'
                    ],
                ]
            )),
        ]);
        $handler = HandlerStack::create($mock);
        $client = $this->getClient($handler);

        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        // Add
        $response = $client->addImportConfiguration($importConfiguration);
        $this->assertArrayHasKey('log', $response);
        $this->assertArrayHasKey('status', $response['log']);
        $this->assertArrayHasKey('message', $response['log']);
        $this->assertEquals($response['log']['status'], 'new');

        // Request an import
        $response = $client->requestImport($importConfiguration->id);
        $this->assertArrayHasKey('log', $response);
        $this->assertArrayHasKey('status', $response['log']);
        $this->assertEquals($response['log']['status'], 'queued');

        // TODO, make smaller test csv and wait ~10 seconds then check status
        // changes from Requested to "Imported"
    }

    /**
     * @group Integrations
     */
    public function testImportConfigurationList()
    {
        $client = $this->getClient();

        $importConfigurations = $this->_importConfigurations();
        foreach ($importConfigurations as $importConfiguration) {
            $response = $client->addImportConfiguration($importConfiguration);
        }

        // Get all configurations
        $response = null;
        $response = $client->getImportConfigurations();

        $this->assertArrayHasKey('ids', $response);
        $this->assertCount(2, $response['ids'], "We added 2 import configurations");

    }
}
