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

    public function testAdd()
    {
        $client = $this->getClient();

        try {
            $client->request('products/product', 'DELETE');
        } catch (\Exception $e) {

        }

        $source = [
            'name' => 'Shirt'
        ];

        $client->request('/products/product/1', 'POST', $source);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/1', 'GET');

        $this->assertSame(
            $response->getData(),
            [
                '_index' => 'products',
                '_type' => 'product',
                '_id' => '1',
                '_version' => 1,
                'found' => true,
                '_source' => $source,
            ]
        );
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
