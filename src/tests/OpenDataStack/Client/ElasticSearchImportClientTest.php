<?php

namespace OpenDataStack\Client;

use PHPUnit\Framework\TestCase;
use OpenDataStack\Client\ElasticSearchImportClient;

/**
 * @group functional
 */
class ElasticSearchImportClientTest extends TestCase
{

    private $_client;

    private function _importConfigurations() {
        // load list of json files from Requests/
        return [];
    }

    public function setUp()
    {
        $this->_client = new ElasticSearchImportClient("http://192.168.99.100:8088/ping", "283y2daksjn");
    }

    /**
    * @todo Pseudo Code
    */
    public function testImportConfigurationAdd() {
        // Test data in ./Examples/Requests/
        $importConfigurations = $this->_importConfigurations();
        $importConfiguration = array_pop($importConfigurations);

        $response = $this->_client->addImportConfiguration($jobConfig);
        $this->assertTrue($response->status);
        $this->assertArrayHasKey('log', $response->data);
        $this->assertArrayHasKey('status', $response->data['log']);
        $this->assertArrayHasKey('message', $response->data['log']);
        $this->assertEquals($response->data['log']['status'], 'New');
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
