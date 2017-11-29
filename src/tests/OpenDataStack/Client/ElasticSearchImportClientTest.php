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
class ElasticSearchImportClientTest extends TestCase
{

    private function _importConfigurations()
    {
        // load list of json files from Requests/
        $dir = new \DirectoryIterator(dirname(__FILE__) . "/Examples/Requests/");
        $importConfirgurations = [];

        foreach ($dir as $fileinfo) {
            if (preg_match("/^.+\.json$/i", $fileinfo->getFilename())) {
                $importConfirgurations[] = json_decode(file_get_contents($fileinfo->getPath() . '/' . $fileinfo->getFilename(), true));
            }
        }

        return $importConfirgurations;
    }

    private function _client($handler)
    {
        return new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn", $handler);
    }

    /**
     * @group Units
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
        $client = $this->_client($handler);
        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        $response = $client->addImportConfiguration($importConfiguration);
        $this->assertArrayHasKey('log', $response);
        $this->assertArrayHasKey('status', $response['log']);
        $this->assertArrayHasKey('message', $response['log']);
        $this->assertEquals($response['log']['status'], 'new');
    }

    /**
     * @group Units
     */
    public function testImportConfigurationDelete()
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
                    //todo: improve this by loading a test config
                ]
            )),
            new Response(200, []),
            new Response(404, []),
        ]);
        $handler = HandlerStack::create($mock);
        $client = $this->_client($handler);

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
     * @group Units
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
        $client = $this->_client($handler);

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
     * @group Unit
     */
    public function testImportConfigurationList()
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
                ],
                [
                    'id' => '11111111-582c-4f29-b1e4-113781e58e3b',
                    'log' => [
                        'status' => 'new',
                        'message' => 'Success'
                    ],
                ]
            ))
        ]);
        $handler = HandlerStack::create($mock);
        $client = $this->_client($handler);

        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        foreach ($importConfigurations as $importConfiguration) {
            $response = $client->addImportConfiguration($importConfiguration);
        }

        $response = $client->getImportConfigurations();
        var_dump($response);
        // delete and return 0
    }

}
