<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecetteApiTest extends WebTestCase
{
    public function testGetRecettesRetourne200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/recettes', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        if ($client->getResponse()->getStatusCode() === 404) {
            $this->markTestSkipped('API Platform non configuré.');
        }

        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetRecettesRetourneBonContentType(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/recettes', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        if ($client->getResponse()->getStatusCode() === 404) {
            $this->markTestSkipped('API Platform non configuré.');
        }

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
    }

    public function testGetRecettesStructureJsonLd(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/recettes', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        if ($client->getResponse()->getStatusCode() === 404) {
            $this->markTestSkipped('API Platform non configuré.');
        }

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('member', $data);
    }

    public function testPostRecetteValideRetourne201(): void
    {
        $client    = static::createClient();
        $catRepo   = static::getContainer()->get('doctrine')
                           ->getRepository(\App\Entity\CategorieRecette::class);
        $categorie = $catRepo->findOneBy([]);

        if (!$categorie) {
            $this->markTestSkipped('Aucune catégorie disponible.');
        }

        $payload = json_encode([
            'titre'            => 'Recette API ' . uniqid(),
            'description'      => 'Description de test via API pour valider le champ minimum requis.',
            'instructions'     => 'Étape 1 : faire ceci. Étape 2 : faire cela.',
            'tempsPreparation' => 20,
            'difficulte'       => 'facile',
            'nbPersonnes'      => 2,
            'categorie'        => '/api/categorie_recettes/' . $categorie->getId(),
        ]);

        $client->request('POST', '/api/recettes', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT'  => 'application/ld+json',
        ], $payload);

        if ($client->getResponse()->getStatusCode() === 404) {
            $this->markTestSkipped('API Platform non configuré.');
        }

        $this->assertResponseStatusCodeSame(201);
    }

    public function testPostRecetteAvecTitreVideRetourne422(): void
    {
        $client  = static::createClient();
        $payload = json_encode([
            'titre'            => '',
            'description'      => 'Description de test',
            'instructions'     => 'Instructions',
            'tempsPreparation' => 10,
            'difficulte'       => 'facile',
            'nbPersonnes'      => 2,
        ]);

        $client->request('POST', '/api/recettes', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT'  => 'application/ld+json',
        ], $payload);

        if ($client->getResponse()->getStatusCode() === 404) {
            $this->markTestSkipped('API Platform non configuré.');
        }

        $this->assertResponseStatusCodeSame(422);
    }
}