<?php

namespace AppBundle\Tests\Elastic;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectTest extends KernelTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    public function test()
    {
        $client = $this->getClient();

        $serverStatus = $client->getStatus()->getServerStatus();

        /* @var $this \PHPUnit_Framework_TestCase */
        $this->assertSame($serverStatus['status'], 200);
        $this->assertSame($serverStatus['name'], 'Fan Boy');
        $this->assertSame($serverStatus['cluster_name'], 'elasticsearch');

        $this->assertTrue($client->getConnection()->isEnabled());
    }

    /**
     * @return \FOS\ElasticaBundle\Elastica\Client
     */
    private function getClient()
    {
        /* @var $client \FOS\ElasticaBundle\Elastica\Client */
        $client = static::$kernel->getContainer()->get('fos_elastica.client.default');

        return $client;
    }
}
