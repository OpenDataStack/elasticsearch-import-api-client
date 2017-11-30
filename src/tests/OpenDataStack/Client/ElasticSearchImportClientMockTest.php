<?php

namespace OpenDataStack\Tests;

use PHPUnit\Framework\TestCase;
use OpenDataStack\Client\ElasticSearchImportClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * @group functional
 */
class ElasticSearchImportClientMockTest extends ElasticSearchImportClientIntegrationTest
{
    /**
     * @group Mock
     */
    public function testImportConfigurationAdd()
    {
        // TODO: TEST CASE FOR NOT FOUND RESOURCE
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            // testImportConfigurationAdd
            new Response(200, [], json_encode(
                [
                    'id' => '11111111-582c-4f29-b1e4-113781e18e3b',
                    'log' => [
                        'status' => 'new',
                        'message' => 'Success'
                    ],
                ]
            )),
        ]);
        $handler = HandlerStack::create($mock);
        $this->setClient(new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn", $handler));
        parent::testImportConfigurationAdd();
    }

    /**
     * Note thqt $client is passed by ElasticSearchImportClientIntegrationTest when
     * we want to avoid mocking and do a live integration test against the server
     * @group Mocks
     */
    public function testImportConfigurationDelete($client = null)
    {
        if ($client == null) {
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
                        //todo: improve this by loading a test config
                    ]
                )),
                new Response(200, []),
                new Response(404, []),
            ]);
            $handler = HandlerStack::create($mock);
            $client = new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn", $handler);
        }

        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        // Add
        $response = $client->addImportConfiguration($importConfiguration);
        $this->assertArrayHasKey('log', $response);
        $this->assertArrayHasKey('status', $response['log']);
        $this->assertArrayHasKey('message', $response['log']);
        $this->assertEquals($response['log']['status'], 'new');

        // Confirm add worked
        $response = $client->getImportConfiguration($importConfiguration->id);
        $this->assertEquals($importConfiguration->id, $response['id']);

        // Delete
        $response = $client->deleteImportConfiguration($importConfiguration->id);
        $this->assertEquals('200', $response);

        // Confirm delete worked
        $response = $client->getImportConfiguration($importConfiguration->id);
        $this->assertEquals(false, $response);
    }

    /**
     * @group Mocks
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
     * @group Mocks
     */
    public function testImportConfigurationList()
    {
        $mock = new MockHandler([
            // testImportConfigurationAdd
            new Response(200, [], json_encode(
                [
                    'id' => '11111111-582c-4f29-b1e4-113781e18e3b',
                    'log' => [
                        'status' => 'new',
                        'message' => 'Success'
                    ]
                ]
            )),
            new Response(200, [], json_encode(
                [
                    'id' => '22222222-582c-4f29-b1e4-113781e18e3b',
                    'log' => [
                        'status' => 'new',
                        'message' => 'Success'
                    ]
                ]
            )),
            new Response(200, [], json_encode(
                [
                    'id' => ['11111111-582c-4f29-b1e4-113781e18e3b', '22222222-582c-4f29-b1e4-113781e18e3b'],
                ]
            )),
            new Response(200, []),
            new Response(200, []),
            new Response(404, []),
        ]);
        $handler = HandlerStack::create($mock);
        $client = $this->getClient($handler);

        $importConfigurations = $this->_importConfigurations();
        foreach ($importConfigurations as $importConfiguration) {
            $response = $client->addImportConfiguration($importConfiguration);
        }
        // Get all configurations
        $response = null;
        $response = $client->getImportConfigurations();
        $this->assertArrayHasKey('id', $response);

        // Delete all configurations
        foreach ($response['id'] as $importConfigurationId) {
            $response = $client->deleteImportConfiguration($importConfigurationId);
        }

        // Get all configurations after deleting: It should return Null
        $response = null;
        $response = $client->getImportConfigurations();
        $this->assertEquals(false, $response);
    }
}