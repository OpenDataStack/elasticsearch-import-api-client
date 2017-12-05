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
        parent::testImportConfigurationDelete($this->getClient());
    }

    /**
     * @group Integrations
     */
    public function testImportRequest($client = null)
    {
        parent::testImportRequest($this->getClient());
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
