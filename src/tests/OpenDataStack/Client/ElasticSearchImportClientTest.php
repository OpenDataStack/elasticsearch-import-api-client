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

    private function _importConfigurations() {
        // load list of json files from Requests/
        $dir = new \DirectoryIterator(dirname(__FILE__) . "/Examples/Requests/");
        $importConfirgurations = [];

        foreach ($dir as $fileinfo) {
            if (preg_match("/^.+\.json$/i" , $fileinfo->getFilename())) {
                $importConfirgurations[] = json_decode(file_get_contents($fileinfo->getPath() . '/' . $fileinfo->getFilename(), true));
            }
        }

        return $importConfirgurations;
    }

    private function _client($handler) {
        return new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn", $handler);
    }

    /**
     * @group Unit
     */
    public function testImportConfigurationAdd() {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            // testImportConfigurationAdd
            new Response(200, [], json_encode(
                [
                    'id' => '11111111-582c-4f29-b1e4-113781e18e3b',
                    'log' => [
                        'status' => 'New',
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
        $this->assertEquals($response['log']['status'], 'New');
    }

    /**
     * @todo Pseudo Code
     */
    public function testImportConfigurationDelete() {
        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        // Add
        $response = $this->_client->addImportConfiguration($importConfiguration);
        $this->assertTrue($response->status);

        // Confirm add worked
        $response = $this->_client->getImportConfiguration($importConfiguration['id']);
        $this->assertTrue($response->status);
        $this->assertEquals($importConfiguration, $response->data);

        // Delete
        $response = $this->_client->deleteImportConfiguration($importConfiguration['id']);
        $this->assertTrue($response->status);

        // Confirm delete worked
        $response = $this->_client->getImportConfiguration($importConfiguration['id']);
        $this->assertFalse($response->status);
    }

    /**
     * @todo Pseudo Code
     */
    public function testImportRequest()
    {
        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        // Add
        $response = $this->_client->addImportConfiguration($importConfiguration);
        $this->assertTrue($response->status);

        // Request an import
        $response = $this->_client->requestImport($importConfiguration['id']);
        $this->assertTrue($response->status);

        $this->assertArrayHasKey('log', $response->data);
        $this->assertArrayHasKey('status', $response->data['log']);
        $this->assertArrayHasKey($response->data['log']['status'], 'Requested');

        // TODO, make smaller test csv and wait ~10 seconds then check status
        // changes from Requested to "Imported"
    }

    /**
     * @todo Pseudo Code
     */
    public function testImportConfigurationList()
    {
        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        foreach ($importConfigurations as $importConfiguration) {
            $response = $this->_client->addImportConfiguration($importConfiguration);
            $this->assertTrue($response->status);
        }

        $remoteImportConfigurations = $this->_client->importConfigurations();
        // TODO: Code logic to set match all keys in $remoteImportConfigurations must be in $localImportConfigurations
    }

}
