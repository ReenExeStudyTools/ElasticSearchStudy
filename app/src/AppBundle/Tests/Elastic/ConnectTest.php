<?php

namespace AppBundle\Tests\Elastic;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectTest extends KernelTestCase
{
    public function test()
    {
        static::bootKernel();
        /* @var $client \FOS\ElasticaBundle\Elastica\Client */
        $client = static::$kernel->getContainer()->get('fos_elastica.client.default');

        $serverStatus = $client->getStatus()->getServerStatus();

        /* @var $this \PHPUnit_Framework_TestCase */
        $this->assertSame($serverStatus['status'], 200);
    }
}
