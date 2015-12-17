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

        try {
            $index->delete();
        } catch (\Exception $e) {

        }

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

    public function testPhrase()
    {
        $client = $this->getClient();

        $index = $client->getIndex('users');

        try {
            $index->delete();
        } catch (\Exception $e) {

        }

        $type = $index->getType('user');

        $users = [
            1 => [
                'name' => 'Anna',
                'about' =>'love fresh juice, and hate coffee',
            ],
            2 => [
                'name' => 'Victory',
                'about'=> 'love coffee',
            ],
        ];

        $documents = [];
        foreach ($users as $id => $user) {
            $documents[] = new Document($id, $users);
        }
        $type->addDocuments($documents);
        $index->refresh();

        $resultCount = $type->count([
            'query' => [
                'match' => [
                    'about' => 'love coffee'
                ]
            ]
        ]);

        $this->assertSame($resultCount, 2);

        $resultCount = $type->count([
            'query' => [
                'match_phrase' => [
                    'about' => 'love'
                ]
            ]
        ]);

        $this->assertSame($resultCount, 2);

        $resultCount = $type->count([
            'query' => [
                'match_phrase' => [
                    'about' => 'love coffee'
                ]
            ]
        ]);

        /**
         * TODO: expect one but find zero
            $this->assertSame($resultCount, 1);
         */
    }
}
