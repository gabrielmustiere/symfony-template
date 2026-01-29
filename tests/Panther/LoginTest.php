<?php

declare(strict_types=1);

namespace App\Tests\Panther;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;

class LoginTest extends PantherTestCase
{
    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testLoginFlow(): void
    {
        $client = static::createPantherClient(['browser' => static::CHROME]);

        // Accéder à la page de connexion
        $crawler = $client->request('GET', '/login');

        // Vérifier qu'on est bien sur la page de login
        $this->assertSelectorTextContains('h2', 'Se Connecter');

        // Remplir et soumettre le formulaire de connexion
        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'admin@example.com',
            '_password' => 'password',
        ]);

        $client->submit($form);

        // Attendre que la page soit chargée (chercher un élément présent sur la page d'accueil)
        $client->waitForVisibility('.lg\\:pl-72');

        // Vérifier qu'on est bien connecté via le contenu de la page
        $this->assertSelectorTextContains('body', 'Dashboard');
        $this->assertSelectorTextContains('body', 'Lorem ipsum');
    }
}
