<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 21; $i++) {
            $user = new User();
            $user->setEmail('user_'.$i.'@example.com');
            $user->setPassword(password_hash('pwd____'.$i, PASSWORD_DEFAULT));
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $random=rand(1, 20);
            for($j=0;$j<$random;$j++){
                $book = new Book();
                $book->setName('book_'.$j);
                $book->setAuthor('author_'.$j);
                $book->setCover('');
                $book->setFile('');
                $book->setUserId($user);
                $manager->persist($book);
            }
        }

        $manager->flush();
    }
}
