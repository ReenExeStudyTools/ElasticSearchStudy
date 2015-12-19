<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;

class MappingTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $index = $client->getIndex('users');
        try {
            $index->delete();
        } catch (\Exception $e) {}

        $type = $index->getType('user');

        $type->addDocument(
            new Document(1, [
                'user_id' => 1,
                'name' => 'Alex',
                'interests' => ['music'],
                'age' => 23,
                'gender' => 'men',
                'register' => date('Y-m-d'),
                'login' => date('Y-m-d H:i:s'),
                'skills' => [
                    [
                        'name' => 'music',
                        'year' => 3,
                        'additional' => [null]
                    ],
                    [
                        'name' => 'architect',
                        'year' => 1,
                    ]
                ],
                'geo' => [
                    'latitude' => 1,
                    'longitude' => 1,
                ]
            ])
        );

        $index->refresh();

        $response = $client->request('users/_mapping/user');

        /**
         * Why different order of fileds?
         */
        $this->assertSame($response->getData(), [
            'users' => [
                'mappings' => [
                    'user' => [
                        'properties' => [
                            'age' => [
                                'type' => 'long'
                            ],
                            'gender' => [
                                'type' => 'string'
                            ],
                            'geo' => [
                                'properties' => [
                                    'latitude' => [
                                        'type' => 'long'
                                    ],
                                    'longitude' => [
                                        'type' => 'long'
                                    ]
                                ]
                            ],
                            'interests' => [
                                'type' => 'string'
                            ],
                            'login' => [
                                'type' => 'string'
                            ],
                            'name' => [
                                'type' => 'string'
                            ],
                            'register' => [
                                'type' => 'date',
                                'format' => 'dateOptionalTime'
                            ],
                            'skills' => [
                                'properties' => [
                                    'name' => [
                                        'type' => 'string'
                                    ],
                                    'year' => [
                                        'type' => 'long'
                                    ]
                                ]
                            ],
                            'user_id' => [
                                'type' => 'long'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
