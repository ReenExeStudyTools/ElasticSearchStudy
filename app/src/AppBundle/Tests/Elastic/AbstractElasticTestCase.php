<?php

namespace AppBundle\Tests\Elastic;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractElasticTestCase extends KernelTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    /**
     * @return \FOS\ElasticaBundle\Elastica\Client
     */
    protected function getClient()
    {
        /* @var $client \FOS\ElasticaBundle\Elastica\Client */
        $client = static::$kernel
            ->getContainer()
            ->get('fos_elastica.client.default');

        return $client;
    }
}
