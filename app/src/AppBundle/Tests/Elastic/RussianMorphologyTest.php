<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;
use Elastica\Multi\ResultSet;
use Elastica\Type;

class RussianMorphologyTest extends AbstractElasticTestCase
{
    public function testSimple()
    {
        $client = $this->getClient();
        $index = $client->getIndex('genders');
        $this->clearIndex($index);
        $type = $index->getType('gender');

        $this->fill($type);
        $index->refresh();

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'match' => [
                    'body' => 'женское'
                ]
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

        $this->fill($type);
        $index->refresh();

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'query_string' => [
                    'fields' => ['body'],
                    'query' => 'женское'
                ]
            ]
        ]);

        $response = $resultSet->getResponse()->getData();
        $this->assertSame($response['hits']['total'], 5);

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'query_string' => [
                    'query' => 'женское'
                ]
            ]
        ]);

        $response = $resultSet->getResponse()->getData();
        $this->assertSame($response['hits']['total'], 5);

        /* @var $resultSet ResultSet */
        $resultSet = $type->search([
            'query' => [
                'match' => [
                    'body' => 'женское'
                ]
            ]
        ]);

        $response = $resultSet->getResponse()->getData();
        $this->assertSame($response['hits']['total'], 5);
    }

    private function fill(Type $type)
    {
        $sources = [
            ['body' => 'женский наряд'],
            ['body' => 'женское украшение'],
            ['body' => 'женская одежда'],
            ['body' => 'женские перчатки'],
            ['body' => 'женскую обувь'],
        ];
        foreach ($sources as $id => $source) {
            $type->addDocument(new Document($id, $source));
        }
    }
}
