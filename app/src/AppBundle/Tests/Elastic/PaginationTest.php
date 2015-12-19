<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;

class PaginationTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $index = $client->getIndex('pagination');
        try {
            $index->delete();
        } catch (\Exception $e) {

        }

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

        $resultSet = $type->search([
            'from' => 90,
            'size' => 20,
        ]);

        $response = $resultSet->getResponse()->getData();
        $this->assertSame($response['hits']['total'], 100);
        $this->assertSame(count($response['hits']['hits']), 10);
    }
}
