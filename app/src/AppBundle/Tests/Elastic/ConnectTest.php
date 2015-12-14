<?php

namespace AppBundle\Tests\Elastic;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectTest extends KernelTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    public function testConnect()
    {
        $client = $this->getClient();

        $serverStatus = $client->getStatus()->getServerStatus();

        /* @var $this \PHPUnit_Framework_TestCase */
        $this->assertSame($serverStatus['status'], 200);
        $this->assertSame($serverStatus['name'], 'Fan Boy');
        $this->assertSame($serverStatus['cluster_name'], 'elasticsearch');

        $this->assertTrue($client->getConnection()->isEnabled());
    }

    public function test()
    {
        $client = $this->getClient();

        try {
            $client->request('/products', 'DELETE');
        } catch (\Exception $e) {

        }

        $sourceProduct = [
            'name' => 'Black Shirt'
        ];

        $client->request('/products/product/1', 'PUT', $sourceProduct);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/1');

        $this->assertSame(
            $response->getData(),
            [
                '_index' => 'products',
                '_type' => 'product',
                '_id' => '1',
                '_version' => 1,
                'found' => true,
                '_source' => $sourceProduct,
            ]
        );

        $sourceCategory = [
            'alias' => 'shirt',
            'code' => 3,
            'name' => 'Shirt',
        ];

        $client->request('/products/category/3', 'PUT', $sourceCategory);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/category/3');

        $this->assertSame(
            $response->getData(),
            [
                '_index' => 'products',
                '_type' => 'category',
                '_id' => '3',
                '_version' => 1,
                'found' => true,
                '_source' => $sourceCategory,
            ]
        );

        $beforeRefreshSearchHits = [
            'total' => 0,
            'max_score' => null,
            'hits' => [],
        ];

        /* @var $resultSet \Elastica\ResultSet */
        $resultSet = $client->getIndex('products')->getType('product')->search();
        $data = $resultSet->getResponse()->getData();
        $this->assertSame($data['hits'], $beforeRefreshSearchHits);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_search');
        $data = $response->getData();
        $this->assertSame($data['hits'], $beforeRefreshSearchHits);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_count');
        $data = $response->getData();
        $this->assertSame($data['count'], 0);

        $count = $client->getIndex('products')->getType('product')->count();
        $this->assertSame($count, 0);

        $client->request('/products/_refresh', 'POST');
        $client->getIndex('products')->refresh();

        $afterRefreshSearchHits = [
            'total' => 1,
            'max_score' => 1.0,
            'hits' => [
                0 => [
                    '_index' => 'products',
                    '_type' => 'product',
                    '_id' => '1',
                    '_score' => 1.0,
                    '_source' => $sourceProduct
                ]
            ]
        ];

        /* @var $resultSet \Elastica\ResultSet */
        $resultSet = $client->getIndex('products')->getType('product')->search();
        $data = $resultSet->getResponse()->getData();
        $this->assertSame($data['hits'], $afterRefreshSearchHits);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_search');
        $data = $response->getData();
        $this->assertSame($data['hits'], $afterRefreshSearchHits);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_count');
        $data = $response->getData();
        $this->assertSame($data['count'], 1);

        $count = $client->getIndex('products')->getType('product')->count();
        $this->assertSame($count, 1);

        /* @var $response \Elastica\Response */
        $exist = $client->request('/products/product/1', 'HEAD');
        $this->assertSame($exist->getStatus(), 200);
        $this->assertTrue($exist->isOk());

        /* @var $response \Elastica\Response */
        $absent = $client->request('/products/product/2', 'HEAD');
        $this->assertSame($absent->getStatus(), 404);
        $this->assertFalse($absent->isOk());
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
