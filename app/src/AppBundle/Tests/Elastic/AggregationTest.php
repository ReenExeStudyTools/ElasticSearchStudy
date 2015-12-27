<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;

class AggregationTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $index = $client->getIndex('users');

        $this->clearIndex($index);

        $type = $index->getType('user');

        $field = 'interests';

        $users = [
            1 => [
                $field => ['programing', 'music']
            ],
            2 => [
                $field => ['fight']
            ],
            3 => [
                $field => ['develop', 'music', 'game']
            ]
        ];

        $documents = [];
        foreach ($users as $id => $user) {
            $documents[] = new Document($id, $user);
        }

        $type->addDocuments($documents);
        $index->refresh();

        $key = 'all_interests';

        $resultSet = $type->search([
            'aggs' => [
                $key => [
                    'terms' => [
                        'field' => $field
                    ]
                ]
            ]
        ]);

        $response = $resultSet->getResponse()->getData();

        $this->assertSame(
            $response['aggregations'],
            [
                $key => [
                    'doc_count_error_upper_bound' => 0,
                    'sum_other_doc_count' => 0,
                    'buckets' => [
                        [
                            'key' => 'music',
                            'doc_count' => 2,
                        ],
                        [
                            'key' => 'develop',
                            'doc_count' => 1,
                        ],
                        [
                            'key' => 'fight',
                            'doc_count' => 1,
                        ],
                        [
                            'key' => 'game',
                            'doc_count' => 1,
                        ],
                        [
                            'key' => 'programing',
                            'doc_count' => 1,
                        ],
                    ]
                ]
            ]
        );
    }
}
