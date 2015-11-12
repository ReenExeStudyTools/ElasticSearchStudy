<?php

namespace AppBundle\Tests\Elastic;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectTest extends KernelTestCase
{
    public function test()
    {
        static::bootKernel();
        static::$kernel->getContainer()->get('fos_elastica.provider_registry');
    }
}
