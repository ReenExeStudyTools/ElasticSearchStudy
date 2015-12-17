<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;

class ConnectTest extends AbstractElasticTestCase
{
    public function testConnect()
    {
        $client = $this->getClient();

        $serverStatus = $client->getStatus()->getServerStatus();

        /* @var $this \PHPUnit_Framework_TestCase */
        $this->assertSame($serverStatus['status'], 200);
        $this->assertSame($serverStatus['name'], 'Nekra');
        $this->assertSame($serverStatus['cluster_name'], 'elasticsearch');

        $this->assertTrue($client->getConnection()->isEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @param $sourceProduct
     * @param $beforeRefreshSearchHits
     * @param $afterRefreshSearchHits
     */
    public function testNative(
        $sourceProduct,
        $beforeRefreshSearchHits,
        $afterRefreshSearchHits
    ) {
        $client = $this->getClient();

        try {
            $client->request('/products', 'DELETE');
        } catch (\Exception $e) {

        }

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

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_search');
        $data = $response->getData();
        $this->assertSame($data['hits'], $beforeRefreshSearchHits);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_count');
        $data = $response->getData();
        $this->assertSame($data['count'], 0);

        $client->request('/products/_refresh', 'POST');

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_search');
        $data = $response->getData();
        $this->assertSame($data['hits'], $afterRefreshSearchHits);

        /* @var $response \Elastica\Response */
        $response = $client->request('/products/product/_count');
        $data = $response->getData();
        $this->assertSame($data['count'], 1);

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
     * @dataProvider dataProvider
     * @param $sourceProduct
     * @param $beforeRefreshSearchHits
     * @param $afterRefreshSearchHits
     */
    public function testSugar(
        $sourceProduct,
        $beforeRefreshSearchHits,
        $afterRefreshSearchHits
    ) {
        $client = $this->getClient();

        $index = $client->getIndex('products');
        try {
            $index->delete();
        } catch (\Exception $e) {

        }

        $index->create([], true);
        $type = $index->getType('product');

        $this->assertFalse($type->exists());

        $type->addDocuments([
            new Document(1, $sourceProduct),
        ]);

        $this->assertFalse($type->exists());

        $document = $type->getDocument(1);

        $this->assertSame($document->getData(), $sourceProduct);

        /* @var $resultSet \Elastica\ResultSet */
        $resultSet = $type->search();
        $data = $resultSet->getResponse()->getData();
        $this->assertSame($data['hits'], $beforeRefreshSearchHits);

        $this->assertSame($type->count(), 0);

        $index->refresh();

        $this->assertTrue($type->exists());

        /* @var $resultSet \Elastica\ResultSet */
        $resultSet = $type->search();
        $data = $resultSet->getResponse()->getData();
        $this->assertSame($data['hits'], $afterRefreshSearchHits);

        $this->assertSame($type->count(), 1);
    }

    public function dataProvider()
    {
        yield [
            $sourceProduct = [
                'name' => 'Black Shirt'
            ],
            $beforeRefreshSearchHits = [
                'total' => 0,
                'max_score' => null,
                'hits' => [],
            ],
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
            ]
        ];
    }
}
