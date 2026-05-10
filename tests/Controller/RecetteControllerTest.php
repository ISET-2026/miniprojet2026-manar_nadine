<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RecetteControllerTest extends WebTestCase
{
    public function testPageListeRetourne200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recettes');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testPageListeContientDesCards(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recettes');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.row');
    }

    public function testPageNouvelleInterdite(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recettes/nouvelle');
        $this->assertResponseStatusCodeSame(302);
    }

    private function createTestUser(string $suffix): User
    {
        $em     = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('test_' . $suffix . '_' . uniqid() . '@test.com');
        $user->setPseudo('Test_' . uniqid());
        $user->setRoles(['ROLE_CUISINIER']);
        $user->setPassword($hasher->hashPassword($user, 'test1234'));
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testCreationRecetteParCuisinier(): void
    {
        $client = static::createClient();
        $user   = $this->createTestUser('creation');
        $client->loginUser($user);

        $catRepo   = static::getContainer()->get('doctrine')
                           ->getRepository(\App\Entity\CategorieRecette::class);
        $categorie = $catRepo->findOneBy([]);

        if (!$categorie) {
            $this->markTestSkipped('Aucune catégorie disponible.');
        }

        $crawler = $client->request('GET', '/recettes/nouvelle');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer la recette')->form([
            'recette[titre]'            => 'Recette test ' . uniqid(),
            'recette[description]'      => 'Description longue de test pour valider le champ minimum requis.',
            'recette[instructions]'     => 'Étape 1 : préparer. Étape 2 : cuire. Étape 3 : servir.',
            'recette[tempsPreparation]' => 30,
            'recette[nbPersonnes]'      => 4,
            'recette[difficulte]'       => 'facile',
            'recette[categorie]'        => $categorie->getId(),
        ]);

        $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testFlashMessageApresCreation(): void
    {
        $client = static::createClient();
        $user   = $this->createTestUser('flash');
        $client->loginUser($user);

        $catRepo   = static::getContainer()->get('doctrine')
                           ->getRepository(\App\Entity\CategorieRecette::class);
        $categorie = $catRepo->findOneBy([]);

        if (!$categorie) {
            $this->markTestSkipped('Aucune catégorie disponible.');
        }

        $crawler = $client->request('GET', '/recettes/nouvelle');

        $form = $crawler->selectButton('Créer la recette')->form([
            'recette[titre]'            => 'Flash test ' . uniqid(),
            'recette[description]'      => 'Description test pour le flash message minimum requis.',
            'recette[instructions]'     => 'Instructions de test pour le message flash.',
            'recette[tempsPreparation]' => 15,
            'recette[nbPersonnes]'      => 2,
            'recette[difficulte]'       => 'moyen',
            'recette[categorie]'        => $categorie->getId(),
        ]);

        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }
}