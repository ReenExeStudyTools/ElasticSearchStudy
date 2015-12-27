<?php

namespace AppBundle\Tests\Elastic;

use Elastica\Document;
use Elastica\Index;
use Elastica\ResultSet;

class AliasTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $old = $client->getIndex('old');
        $new = $client->getIndex('new');
        $index = $client->getIndex('products');

        /* @var $clearIndex Index */
        foreach ([$old, $new, $index] as $clearIndex) {
            try {
                $clearIndex->delete();
            } catch (\Exception $e) {

            }
        }

        $oldProduct = [
            'name' => 'Blouse',
        ];

        $old->getType('product')->addDocument(new Document(1, $oldProduct));

        $newProduct = [
            'name' => 'Shirt',
        ];

        $new->getType('product')->addDocument(new Document(1, $newProduct));

        $client->refreshAll();

        $old->addAlias('products');

        $this->assertSameFirstProduct($index, $oldProduct);

        $new->addAlias('products', true);

        $this->assertSameFirstProduct($index, $newProduct);
    }

    private function assertSameFirstProduct(Index $index, array $product)
    {
        /* @var $resultSet ResultSet */
        $resultSet = $index->search([
            'query' => [
                'match_all' => []
            ]
        ]);

        $response = $resultSet->getResponse()->getData();

        $this->assertSame($response['hits']['hits'][0]['_source'], $product);
    }
}
