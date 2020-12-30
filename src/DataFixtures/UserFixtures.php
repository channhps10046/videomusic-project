<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $password_encoder)
    {
        $this->password_encoder = $password_encoder;
    }
    public function load(ObjectManager $manager)
    {
        foreach ($this->userData() as [$name, $lastname, $email, $password, $roles]) {
            $user = new User();
            $user->setName($name);
            $user->setLastName($lastname);
            $user->setEmail($email);
            $user->setPassword($this->password_encoder->encodePassword($user, $password));
            $user->setRoles($roles);
            $manager->persist($user);
        }
        $manager->flush();
    }

    private function userData(): array
    {
        return [
            ['John', 'Wayne', 'jw@symf4.loc', 'passw', ['ROLE_ADMIN']],
            ['John', 'Wayne2', 'jw2@symf4.loc', 'passw', ['ROLE_ADMIN']],
            ['John', 'Doe', 'jd@symf4.loc', 'passw', ['ROLE_USER']]
        ];
    }
}
