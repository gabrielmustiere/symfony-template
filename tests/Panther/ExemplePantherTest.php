<?php

declare(strict_types=1);

namespace App\Tests\Panther;

use Symfony\Component\Panther\PantherTestCase;

class ExemplePantherTest extends PantherTestCase
{
    public function testSomething(): void
    {
        $client = static::createPantherClient(['browser' => static::CHROME]);

        $client->request('GET', '/');

        $this->assertSelectorTextContains('html', 'Lorem');
    }
}
