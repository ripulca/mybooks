<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function add(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllBooksById($id, $currentPage = 1)  //получить книги конкретного пользователя
    {
        $query = $this->createQueryBuilder('b')
            ->andWhere('b.user_id =:val')
            ->setParameter('val', $id)
            ->orderBy('b.last_reading_date', 'DESC')
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage);

        return $paginator;
    }

    public function getAllBooks($currentPage = 1)       //получить все книги
    {
        $query = $this->createQueryBuilder('b')
            ->orderBy('b.last_reading_date', 'DESC')
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage);

        return $paginator;
    }

    
    public function paginate($dql, $page = 1, $limit = 8)       //пагинация
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }
//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function updateReadingDate($id, $user_id, $date): array
//    {
//        return $this->createQueryBuilder('UPDATE book SET reading_date=:val WHERE id=:id AND `user_id_id=:user_id_id;')
//            ->setParameter('val', $date)
//            ->setParameter('id', $id)
//            ->setParameter('user_id_id', $user_id)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
