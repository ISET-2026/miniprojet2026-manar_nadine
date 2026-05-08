<?php

namespace App\DataFixtures;

use App\Entity\Recette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RecetteFixtures extends Fixture implements DependentFixtureInterface
{
    public const TITRES = [
        'Tarte aux pommes caramélisées',
        'Risotto aux champignons et parmesan',
        'Soupe de lentilles épicée',
        'Salade niçoise traditionnelle',
        'Poulet rôti aux herbes de Provence',
        'Tiramisu au café et amaretto',
        'Quiche lorraine maison',
        'Ratatouille provençale',
        'Boeuf bourguignon mijotée',
        'Crêpes Suzette au Grand Marnier',
        'Velouté de butternut au gingembre',
        'Pizza margherita à la napolitaine',
        'Gratin dauphinois crémeux',
        'Mousse au chocolat noir intense',
        'Couscous royal aux légumes',
        'Tarte tatin aux poires',
        'Gâteau basque à la crème',
        'Brandade de morue à la provençale',
        'Smoothie bowl mangue-fruits rouges',
        'Bruschetta aux tomates et basilic',
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $nbCategories = count(CategorieRecetteFixtures::CATEGORIES);
        $nbTags       = count(TagRecetteFixtures::TAGS);
        $nbUsers      = 7; // admin + chef + 5 faker users

        foreach (self::TITRES as $i => $titre) {
            $recette = new Recette();
            $recette->setTitre($titre);
            $recette->setDescription($faker->paragraph(3));
            $recette->setInstructions($faker->paragraphs(4, true));
            $recette->setTempsPreparation($faker->numberBetween(10, 60));
            $recette->setTempsCuisson($faker->optional(0.7)->numberBetween(15, 120));
            $recette->setDifficulte($faker->randomElement(['facile', 'moyen', 'difficile']));
            $recette->setNbPersonnes($faker->numberBetween(1, 8));
            $recette->setPubliee($faker->boolean(80));
            $recette->setDateCreation($faker->dateTimeBetween('-6 months', 'now'));

            // Catégorie aléatoire
            $catRef = 'categorie-' . $faker->numberBetween(0, $nbCategories - 1);
            $recette->setCategorie($this->getReference($catRef, \App\Entity\CategorieRecette::class));

            // Auteur aléatoire
            $userRefs = ['user-admin', 'user-chef', 'user-0', 'user-1', 'user-2', 'user-3', 'user-4'];
            $recette->setAuteur($this->getReference($faker->randomElement($userRefs), \App\Entity\User::class));

            // Tags aléatoires (1 à 4)
            $nbTags_recette = $faker->numberBetween(1, 4);
            $tagIndexes = (array) $faker->randomElements(range(0, $nbTags - 1), $nbTags_recette);
            foreach ($tagIndexes as $tagIndex) {
                $recette->addTag($this->getReference('tag-' . $tagIndex, \App\Entity\TagRecette::class));
            }

            $manager->persist($recette);
            $this->addReference('recette-' . $i, $recette);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategorieRecetteFixtures::class,
            TagRecetteFixtures::class,
            UserFixtures::class,
        ];
    }
}
