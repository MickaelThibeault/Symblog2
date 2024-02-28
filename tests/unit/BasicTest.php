<?php

namespace App\Tests\unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BasicTest extends KernelTestCase
{
    public function testEnvironnementIsOk(): void
    {
        $this->assertTrue(true);
    }
}