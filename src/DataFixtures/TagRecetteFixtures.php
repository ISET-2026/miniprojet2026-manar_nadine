<?php

namespace App\DataFixtures;

use App\Entity\TagRecette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagRecetteFixtures extends Fixture
{
    public const TAGS = [
        ['nom' => 'Végétarien', 'couleur' => '#2ecc71'],
        ['nom' => 'Végan',      'couleur' => '#27ae60'],
        ['nom' => 'Sans Gluten','couleur' => '#e67e22'],
        ['nom' => 'Bio',        'couleur' => '#8e44ad'],
        ['nom' => 'Rapide',     'couleur' => '#e74c3c'],
        ['nom' => 'Familial',   'couleur' => '#3498db'],
        ['nom' => 'Festif',     'couleur' => '#f39c12'],
        ['nom' => 'Économique', 'couleur' => '#1abc9c'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TAGS as $i => $data) {
            $tag = new TagRecette();
            $tag->setNom($data['nom']);
            $tag->setCouleur($data['couleur']);
            $manager->persist($tag);
            $this->addReference('tag-' . $i, $tag);
        }

        $manager->flush();
    }
}
