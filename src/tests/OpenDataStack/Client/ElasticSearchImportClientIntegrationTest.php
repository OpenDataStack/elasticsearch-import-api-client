<?php

namespace OpenDataStack\Tests;

use PHPUnit\Framework\TestCase;
use OpenDataStack\Client\ElasticSearchImportClient;

/**
 * @group functional
 */
class ElasticSearchImportClientIntegrationTest extends TestCase
{
    private $client;
    private $mockTest;

    public function __construct()
    {
        parent::__construct();
        $this->mockTest = new ElasticSearchImportClientMockTest();
    }

    protected function setMockTest($mockTest)
    {
        $this->mockTest = $mockTest;
    }

    protected function getMockTest()
    {
        return $this->mockTest;
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
    public function testImportConfigurationAdd()
    {
        $this->setClient(new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn"));
        $this->mockTest->testImportConfigurationAdd($this->getClient());
    }

    /**
     * @group Integrations
     */
    public function testImportConfigurationDelete()
    {
        $this->setClient(new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn"));
        $this->mockTest->testImportConfigurationDelete($this->getClient());
    }

    /**
     * @group Integration
     */
    public function testImportRequest()
    {
        $this->setClient(new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn"));
        $this->mockTest->testImportRequest($this->getClient());
        // TODO, make smaller test csv and wait ~10 seconds then check status
        // changes from Requested to "Imported"
    }

    /**
     * @group Integrations
     */
    public function testImportConfigurationList($client = null)
    {
        $this->setClient(new ElasticSearchImportClient("http://localhost:8088", "283y2daksjn"));
        $this->mockTest->testImportConfigurationList($this->getClient());
    }
}
