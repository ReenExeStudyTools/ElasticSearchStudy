<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;
use Elastica\Index;

class FilterTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $index = $client->getIndex('index');

        $this->clearIndex($index);

        $type = $index->getType('type');

        $data = [
            1 => [
                'price' => 10
            ],
            2 => [
                'price' => 20
            ],
            3 => [
                'price' => 20
            ]
        ];

        $documents = [];

        foreach ($data as $key => $value) {
            $documents[] = new Document($key, $value);
        }

        $type->addDocuments($documents);

        $index->refresh();

        $this->assertSearchResult($index, [], []);
    }

    private function assertSearchResult(Index $index, array $query, array $expect)
    {

    }
}
