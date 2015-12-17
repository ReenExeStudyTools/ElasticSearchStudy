<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;
use Elastica\Multi\ResultSet;

class MatchTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $index = $client->getIndex('products');

        $index->delete();

        $type = $index->getType('product');

        $id = 1;
        $source = [
            'name' => 'Black Shirt',
            'price' => 100,
        ];

        $type->addDocument(
            new Document($id, $source)
        );

        $index->refresh();

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'match_all' => []
            ]
        ]);

        $response = $resultSet->getResponse()->getData();

        $this->assertSame(
            $response['hits'],
            [
                'total' => 1,
                'max_score' => 1.0,
                'hits' => [
                    [
                        '_index' => 'products',
                        '_type' => 'product',
                        '_id' => (string)$id,
                        '_score' => 1.0,
                        '_source' => $source,
                    ]
                ]
            ]
        );

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'match' => [
                    'name' => 'Black'
                ]
            ]
        ]);

        $response = $resultSet->getResponse()->getData();

        $expect =             [
            'total' => 1,
            'max_score' => 0.19178301,
            'hits' => [
                [
                    '_index' => 'products',
                    '_type' => 'product',
                    '_id' => (string)$id,
                    '_score' => 0.19178301,
                    '_source' => $source,
                ]
            ]
        ];

        $this->assertSame(
            $response['hits'],
            $expect
        );

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'filtered' => [
                    'filter' => [
                        'range' => [
                            'price' => [
                                'gt' => 30
                            ]
                        ]
                    ],
                    'query' => [
                        'match' => [
                            'name' => 'Black'
                        ]
                    ]
                ]
            ]
        ]);

        $response = $resultSet->getResponse()->getData();

        $this->assertSame(
            $response['hits'],
            $expect
        );
    }
}
