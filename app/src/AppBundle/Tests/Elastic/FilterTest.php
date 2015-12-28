<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;
use Elastica\Index;
use Elastica\Multi\ResultSet;

class FilterTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $index = $client->getIndex('index');

        $this->clearIndex($index);

        $type = $index->getType('type');

        $data = [
            1 => $book = [
                'name' => 'Book',
                'price' => 10
            ],
            2 => $note = [
                'name' => 'Note',
                'price' => 20
            ],
            3 => $phone = [
                'name' => 'Phone',
                'price' => 20
            ]
        ];

        $documents = [];

        foreach ($data as $key => $value) {
            $documents[] = new Document($key, $value);
        }

        $type->addDocuments($documents);

        $index->refresh();

        $dataProvider = [
            [
                10,
                [$book]
            ],
            [
                20,
                [$note, $phone]
            ],
            [
                100,
                []
            ]
        ];

        foreach ($dataProvider as list($price, $expect)) {
            $this->assertSearchResult(
                $index,
                [
                    'query' => [
                        'filtered' => [
                            'query' => [
                                'match_all' => []
                            ],
                            'filter' => [
                                'term' => [
                                    'price' => $price
                                ]
                            ]
                        ]
                    ]
                ],
                $expect
            );
        }
    }

    private function assertSearchResult(Index $index, array $query, array $expect)
    {
        /* @var $resultSet ResultSet */
        $resultSet = $index->search($query);

        $response = $resultSet->getResponse()->getData();

        $this->assertSame(array_column($response['hits']['hits'], '_source'), $expect);
    }
}
