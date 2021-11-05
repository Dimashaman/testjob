<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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

    public function getMinTwoCoAuthorsNativeSql()
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT);
        $rsm->addRootEntityFromClassMetadata(Book::class, 'b');
        $rsm->addJoinedEntityFromClassMetadata(Author::class, 'a', 'b', 'authors');
        $sql = "SELECT {$rsm->generateSelectClause()} FROM 
				(SELECT b.id
                FROM book b
                INNER JOIN author_book ab
                ON b.id = ab.book_id
                GROUP BY b.id
                HAVING COUNT(ab.book_id) >= 2) t
				INNER JOIN book b
                ON t.id = b.id
				INNER JOIN author_book ab
				ON t.id = ab.book_id
				INNER JOIN author a
				ON ab.author_id = a.id";
        $query = $em->createNativeQuery($sql, $rsm);

        return $query->getResult();
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
