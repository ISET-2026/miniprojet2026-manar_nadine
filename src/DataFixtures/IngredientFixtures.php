<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class IngredientFixtures extends Fixture implements DependentFixtureInterface
{
    private const INGREDIENTS_POOL = [
        ['farine',         '250g'],
        ['sucre',          '150g'],
        ['beurre',         '100g'],
        ['oeufs',          '3'],
        ['lait',           '25cl'],
        ['sel',            '1 pincée'],
        ['poivre',         'au goût'],
        ['huile d\'olive', '3 c. à soupe'],
        ['ail',            '2 gousses'],
        ['oignon',         '1 gros'],
        ['tomates',        '400g'],
        ['parmesan',       '80g'],
        ['crème fraîche',  '20cl'],
        ['thym',           '2 branches'],
        ['laurier',        '1 feuille'],
        ['riz arborio',    '300g'],
        ['champignons',    '300g'],
        ['poulet',         '1kg'],
        ['carottes',       '3'],
        ['pommes de terre','500g'],
        ['lardons',        '150g'],
        ['gruyère râpé',   '200g'],
        ['levure chimique','1 sachet'],
        ['vanille',        '1 gousse'],
        ['citron',         '1'],
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $nbRecettes = count(RecetteFixtures::TITRES);

        for ($i = 0; $i < $nbRecettes; $i++) {
            $recette = $this->getReference('recette-' . $i, \App\Entity\Recette::class);
            $nb = $faker->numberBetween(3, 8);
            $pool = $faker->randomElements(self::INGREDIENTS_POOL, $nb);

            foreach ($pool as [$nom, $quantite]) {
                $ingredient = new Ingredient();
                $ingredient->setNom($nom);
                $ingredient->setQuantite($quantite);
                $ingredient->setRecette($recette);
                $manager->persist($ingredient);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [RecetteFixtures::class];
    }
}
