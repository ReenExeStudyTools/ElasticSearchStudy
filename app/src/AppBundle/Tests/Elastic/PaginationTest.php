<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;

class PaginationTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $index = $client->getIndex('pagination');

        $this->clearIndex($index);

        $type = $index->getType('product');

        $documents = [];
        for ($id = 1; $id <= 100; ++$id) {
            $documents[] = new Document($id, [
                'id' => $id
            ]);
        }

        $type->addDocuments($documents);

        $index->refresh();

        $this->assertSame($type->count(), 100);

        foreach ([5, 20, 50] as $size) {
            $resultSet = $type->search([
                'size' => $size,
            ]);

            $response = $resultSet->getResponse()->getData();

            $this->assertSame($response['hits']['total'], 100);
            $this->assertSame(count($response['hits']['hits']), $size);
        }

        $dataProvider = [
            [90, 20, 10],
            [100, 20, 0],
            [200, 20, 0],
            [0, 0, 0],
        ];

        foreach ($dataProvider as list($from, $size, $expect)) {
            $resultSet = $type->search([
                'from' => $from,
                'size' => $size,
            ]);

            $response = $resultSet->getResponse()->getData();
            $this->assertSame($response['hits']['total'], 100);
            $this->assertSame(count($response['hits']['hits']), $expect);
        }
    }
}
