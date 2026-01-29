<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MailpitTest extends WebTestCase
{
    public function testEmailIsSentAndReceivedByMailpit(): void
    {
        $client = static::createClient();

        // 1. Clear Mailpit messages before test
        $httpClient = self::getContainer()->get(HttpClientInterface::class);
        $httpClient->request('DELETE', 'http://localhost:8027/api/v1/messages');

        // 2. Trigger the email sending
        $client->request('GET', '/test-email');
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorTextContains('[data-controller="flash-messages"]', 'Email de test envoyé avec succès !');

        // 3. Verify in Mailpit via API
        // Give it a tiny bit of time for the mailer to process if it's async (though here it should be sync by default in test)
        $response = $httpClient->request('GET', 'http://localhost:8027/api/v1/messages');
        $data = $response->toArray();

        $this->assertGreaterThan(0, $data['total'], 'No messages found in Mailpit');

        $latestEmail = $data['messages'][0];
        $this->assertSame('Test Email from Symfony', $latestEmail['Subject']);
        $this->assertSame('hello@example.com', $latestEmail['From']['Address']);
        $this->assertSame('test@example.com', $latestEmail['To'][0]['Address']);
    }
}
