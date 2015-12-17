<?php

namespace AppBundle\Tests\Elastic;

class AnalyzerTest extends AbstractElasticTestCase
{
    public function test()
    {
        $client = $this->getClient();

        $response = $client->request('_analyze?text=just+do+it');

        $data = $response->getData();

        $this->assertSame($data, [
            'tokens' => [
                [
                    'token' => 'just',
                    'start_offset' => 0,
                    'end_offset' => 4,
                    'type' => '<ALPHANUM>',
                    'position' => 1,
                ],
                [
                    'token' => 'do',
                    'start_offset' => 5,
                    'end_offset' => 7,
                    'type' => '<ALPHANUM>',
                    'position' => 2,
                ],
                [
                    'token' => 'it',
                    'start_offset' => 8,
                    'end_offset' => 10,
                    'type' => '<ALPHANUM>',
                    'position' => 3,
                ],
            ]
        ]);
    }
}
