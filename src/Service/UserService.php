<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{


    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasherInterface,
        protected EntityManagerInterface $entityManager
    )
    {
    }

    public function factory(array $params, $save = true): User
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $params['email']]);

        if (!$user) {
            $user = new User();
            $user->setEmail($params['email']);
            $user->setRoles([$params['roles']]);
            $user->setPassword($this->userPasswordHasherInterface->hashPassword(
                $user,
                $params['plainPassword']
            ));
            if($save){
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }

        return $user;
    }
}