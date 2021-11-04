<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
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

    public function applyFilters(Request $request)
    {
        $qb = $this->createQueryBuilder('b');
        $stringFilters = array_filter(
            [
                'title' => $request->query->get('title'),
                'description' => $request->query->get('description')
            ]
        );
        
        $integerFilters = array_filter(
            [
                'id' => $request->query->get('id'),
                'publishYear' => $request->query->get('publishYear')
            ]
        );

        foreach ($stringFilters as $key => $value) {
            $qb->andWhere('b.'. $key . ' LIKE :val')
            ->setParameter('val', '%' . $value . '%');
        }

        foreach ($integerFilters as $key => $value) {
            $qb->andWhere('b.'. $key . ' = :intval')
            ->setParameter('intval', $value);
        }

        if ($author = $request->query->get('author')) {
            $qb->leftJoin('b.authors', 'a')
            ->andWhere('a.id = :authorId')
            ->setParameter('authorId', intval($author));
        }


        return $qb->getQuery()
        ->getResult();
    }

    // /**
    //  * @return Book[] Returns an array of Book objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
