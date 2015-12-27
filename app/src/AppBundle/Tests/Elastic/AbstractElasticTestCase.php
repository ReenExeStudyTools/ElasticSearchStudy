<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Index;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractElasticTestCase extends KernelTestCase
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

    protected function clearIndex(Index $index)
    {
        try {
            $index->delete();
        } catch (\Exception $e) {}
    }
}
