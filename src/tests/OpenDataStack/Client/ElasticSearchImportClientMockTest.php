<?php

namespace OpenDataStack\Tests;

use PHPUnit\Framework\TestCase;
use OpenDataStack\Client\ElasticSearchImportClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * @group functional
 */
class ElasticSearchImportClientMockTest extends TestCase
{
    protected function _importConfigurations()
    {
        // load list of json files from Requests/
        $dir = new \DirectoryIterator(dirname(__FILE__) . "/Examples/Requests/");
        $importConfigurations = [];

        foreach ($dir as $fileinfo) {
            if (preg_match("/^.+\.json$/i", $fileinfo->getFilename())) {
                $importConfigurations[] = json_decode(file_get_contents($fileinfo->getPath() . '/' . $fileinfo->getFilename(), true));
            }
        }

        return $importConfigurations;
    }

    /**
     * @group Mock
     */
    public function testImportConfigurationAdd($client = null)
    {
        if ($client == null) {
            // TODO: TEST CASE FOR NOT FOUND RESOURCE
            $date = new \DateTime('now');
            $timestamp = $date->format('Y-m-d H:i:s');
            $log = array(
                "status" => "new",
                "message" => "1 created at {$timestamp}",
                "created_at" => $timestamp
            );
            // Create a mock and queue two responses.
            $mock = new MockHandler([
                new Response(200, [], json_encode(
                    [
                        'id' => '11111111-582c-4f29-b1e4-113781e18e3b',
                        'log' => [
                            'status' => 'new',
                            'message' => 'Success',
                            'flag' => $log['status'],
                        ],
                    ]
                )),
            ]);
            $handler = HandlerStack::create($mock);
            $client = new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn", $handler);
        }

        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        try {
            $response = $client->addImportConfiguration($importConfiguration);
            $this->assertArrayHasKey('log', $response);
            $this->assertArrayHasKey('status', $response['log']);
            $this->assertArrayHasKey('flag', $response['log']);
            $this->assertArrayHasKey('message', $response['log']);
            $this->assertEquals($response['log']['flag'], 'new');
        } catch (ClientException $exception) {
            $this->fail("fail with exception code : {$exception->getCode()} and message : {$exception->getMessage()}");
        }
    }

    /**
     * Note thqt $client is passed by ElasticSearchImportClientIntegrationTest when
     * we want to avoid mocking and do a live integration test against the server
     * @group Mock
     */
    public function testImportConfigurationDelete($client = null)
    {
        if ($client == null) {
            $date = new \DateTime('now');
            $timestamp = $date->format('Y-m-d H:i:s');
            $log = array(
                "status" => "new",
                "message" => "1 created at {$timestamp}",
                "created_at" => $timestamp
            );
            $mock = new MockHandler([
                // testImportConfigurationAdd
                new Response(200, [], json_encode(
                    [
                        'id' => '22222222-582c-4f29-b1e4-113781e58e3b',
                        'log' => [
                            'status' => 'new',
                            'message' => 'Success',
                            'flag' => $log['status'],
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

        try {
            // Add configuration
            $response = $client->addImportConfiguration($importConfiguration);
            $this->assertEquals($importConfiguration->id, $response['id']);
            $this->assertArrayHasKey('log', $response);
            $this->assertArrayHasKey('status', $response['log']);
            $this->assertArrayHasKey('message', $response['log']);
            $this->assertEquals($response['log']['flag'], 'new');

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
     * @group Mock
     */
    public function testImportRequest($client = null)
    {
        if ($client == null) {
            $date = new \DateTime('now');
            $timestamp = $date->format('Y-m-d H:i:s');
            $log = array(
                "status" => "new",
                "message" => "1 created at {$timestamp}",
                "created_at" => $timestamp
            );
            $mock = new MockHandler([
                // testImportConfigurationAdd
                new Response(200, [], json_encode(
                    [
                        'id' => '22222222-582c-4f29-b1e4-113781e58e3b',
                        'log' => [
                            'status' => 'new',
                            'message' => 'Success',
                            'flag' => $log['status'],
                        ],
                    ]
                )),
                new Response(200, [], json_encode(
                    [
                        'id' => '22222222-582c-4f29-b1e4-113781e58e3b',
                        'log' => [
                            'status' => 'queued',
                            "message" => 'queued',
                            "flag" => 'queued',
                        ],
                    ]
                )),
            ]);
            $handler = HandlerStack::create($mock);
            $client = new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn", $handler);
        }

        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        // Add
        $response = $client->addImportConfiguration($importConfiguration);

        // Request an import
        $response = $client->requestImport($importConfiguration->id);
        $this->assertEquals($importConfiguration->id, $response['id']);
        $this->assertArrayHasKey('log', $response);
        $this->assertArrayHasKey('status', $response['log']);
        $this->assertEquals($response['log']['status'], 'queued');
        // TODO, make smaller test csv and wait ~10 seconds then check status
        // changes from Requested to "Imported"
    }

    /**
     * @group Mock
     */
    public function testImportConfigurationList($client = null)
    {
        if ($client == null) {
            $date = new \DateTime('now');
            $timestamp = $date->format('Y-m-d H:i:s');
            $log = array(
                "status" => "new",
                "message" => "1 created at {$timestamp}",
                "created_at" => $timestamp
            );
            $mock = new MockHandler([
                // testImportConfigurationAdd
                new Response(200, [], json_encode(
                    [
                        'id' => '11111111-582c-4f29-b1e4-113781e18e3b',
                        'log' => [
                            'status' => 'new',
                            'message' => 'Success',
                            'flag' => $log['status'],
                        ],
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
                        'ids' => ['11111111-582c-4f29-b1e4-113781e18e3b', '22222222-582c-4f29-b1e4-113781e18e3b'],
                    ]
                )),
                new Response(200, []),
                new Response(200, []),
                new Response(404, []),
            ]);
            $handler = HandlerStack::create($mock);
            $client = new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn", $handler);
        }

        $importConfigurations = $this->_importConfigurations();
        foreach ($importConfigurations as $importConfiguration) {
            $response = $client->addImportConfiguration($importConfiguration);
        }
        // Get all configurations
        $response = null;
        $response = $client->getImportConfigurations();
        $this->assertArrayHasKey('ids', $response);

        // Delete all configurations
        foreach ($response['ids'] as $importConfigurationId) {
            $response = $client->deleteImportConfiguration($importConfigurationId);
        }

        // Get all configurations after deleting: It should return Null
        $response = null;
        $response = $client->getImportConfigurations();
        $this->assertEquals(false, $response);
    }
}
