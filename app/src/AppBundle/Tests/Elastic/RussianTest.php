<?php

namespace AppBundle\Tests\Elastic;


use Elastica\Document;
use Elastica\Multi\ResultSet;

class RussianTest extends AbstractElasticTestCase
{
    public function test()
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
                'multi_match' => [
                    'query' => ['body' => 'женское']
                ]
            ]
        ]);

        $response = $resultSet->getResponse()->getData();
        var_dump($response);
    }
}