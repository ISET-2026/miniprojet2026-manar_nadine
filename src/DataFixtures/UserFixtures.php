<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Admin
        $admin = new User();
        $admin->setEmail('admin@recipehub.com');
        $admin->setPseudo('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);
        $this->addReference('user-admin', $admin);

        // Chef cuisinier
        $chef = new User();
        $chef->setEmail('chef@recipehub.com');
        $chef->setPseudo('ChefMaster');
        $chef->setRoles(['ROLE_CUISINIER']);
        $chef->setPassword($this->hasher->hashPassword($chef, 'chef123'));
        $manager->persist($chef);
        $this->addReference('user-chef', $chef);

        // 5 utilisateurs générés par Faker
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPseudo($faker->userName());
            $user->setRoles(['ROLE_CUISINIER']);
            $user->setPassword($this->hasher->hashPassword($user, 'user123'));
            $manager->persist($user);
            $this->addReference('user-' . $i, $user);
        }

        $manager->flush();
    }
}
