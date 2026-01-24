<?php

// src/DataFixtures/UserFixtures.php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_ADMIN = 'user_admin';
    public const USER_MANAGER = 'user_manager';

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        // Admin
        $admin = new User();
        $admin->setName('Admin User');
        $admin->setEmail('admin@renzicom.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRole('ROLE_ADMIN');
        $admin->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($admin);
        $this->addReference(self::USER_ADMIN, $admin); // ✅ Pas de classe nécessaire pour addReference

        // Manager
        $managerUser = new User();
        $managerUser->setName('Eddy');
        $managerUser->setEmail('eddy@renzicom.com');
        $managerUser->setPassword($this->passwordHasher->hashPassword($managerUser, 'testeddy'));
        $admin->setCreatedAt(new \DateTimeImmutable());
        $managerUser->setRole('ROLE_ADMIN');
        $manager->persist($managerUser);
        $this->addReference(self::USER_MANAGER, $managerUser);

        // 10 utilisateurs normaux
        for ($i = 1; $i <= 10; ++$i) {
            $user = new User();
            $user->setName($faker->name());
            $user->setEmail($faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setRole($faker->randomElement(['ROLE_DRIVER', 'ROLE_STATISTICS']));
            $admin->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($user);
            $this->addReference('user_'.$i, $user);
        }

        $manager->flush();
    }
}
