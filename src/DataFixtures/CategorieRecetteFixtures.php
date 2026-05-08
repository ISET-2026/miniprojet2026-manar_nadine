<?php

namespace App\DataFixtures;

use App\Entity\CategorieRecette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieRecetteFixtures extends Fixture
{
    public const CATEGORIES = [
        ['nom' => 'Entrée',  'icone' => '🥗', 'description' => 'Salades, soupes froides, amuse-bouches'],
        ['nom' => 'Plat',    'icone' => '🍝', 'description' => 'Plats principaux chauds ou froids'],
        ['nom' => 'Dessert', 'icone' => '🍰', 'description' => 'Gâteaux, tartes, glaces et douceurs'],
        ['nom' => 'Boisson', 'icone' => '🥤', 'description' => 'Smoothies, jus, cocktails et infusions'],
        ['nom' => 'Snack',   'icone' => '🍕', 'description' => 'En-cas, chips, pizzas et finger food'],
        ['nom' => 'Soupe',   'icone' => '🥣', 'description' => 'Veloutés, bouillons et potages'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $i => $data) {
            $categorie = new CategorieRecette();
            $categorie->setNom($data['nom']);
            $categorie->setIcone($data['icone']);
            $categorie->setDescription($data['description']);
            $manager->persist($categorie);
            $this->addReference('categorie-' . $i, $categorie);
        }

        $manager->flush();
    }
}
