<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;
use Elastica\Multi\ResultSet;

class RussianMorphologyTest extends AbstractElasticTestCase
{
    public function testSimple()
    {
        $client = $this->getClient();
        $index = $client->getIndex('genders');
        $this->clearIndex($index);
        $type = $index->getType('gender');

        $id = 1;
        $sources = [
            ['body' => 'женский наряд'],
            ['body' => 'женское украшение'],
            ['body' => 'женская одежда'],
            ['body' => 'женские перчатки'],
            ['body' => 'женскую обувь'],
        ];

        foreach ($sources as $source) {
            $type->addDocument(
                new Document($id++, $source)
            );
        }
        $index->refresh();

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'term' => ['body' => 'женское']
            ]
        ]);

        $response = $resultSet->getResponse()->getData();
        $this->assertSame($response['hits']['total'], 1);
    }

    public function testAnalyzer()
    {
        $client = $this->getClient();
        $index = $client->getIndex('genders');
        $this->clearIndex($index);
        $type = $index->getType('gender');

        $client->request('genders', 'PUT', [
            'mappings' => [
                'gender' => [
                    '_all' => [
                        'analyzer' => 'russian_morphology'
                    ],
                    'properties' => [
                        'body' => [
                            'type' => 'string',
                            'analyzer' => 'russian_morphology'
                        ]
                    ]
                ]
            ]
        ]);

        $id = 1;
        $sources = [
            ['body' => 'женский наряд'],
            ['body' => 'женское украшение'],
            ['body' => 'женская одежда'],
            ['body' => 'женские перчатки'],
            ['body' => 'женскую обувь'],
        ];

        foreach ($sources as $source) {
            $type->addDocument(
                new Document($id++, $source)
            );
        }
        $index->refresh();

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'query_string' => ['query' => 'body:женское']
            ]
        ]);

        $response = $resultSet->getResponse()->getData();
        $this->assertSame($response['hits']['total'], 5);
    }
}
