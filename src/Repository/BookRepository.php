<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Author;
use App\Dto\BookFilterDto;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Book[]    findByMinCoAuthorsNativeSql()
 * @method Book[]    findByMinCoAuthorsDql()
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByMinCoAuthorsNativeSql(int $authorsAmount)
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $bookIdsWithNAuthorsSql = "SELECT b.id
            FROM book b
            INNER JOIN author_book ab
            ON b.id = ab.book_id
            GROUP BY b.id
            HAVING COUNT(ab.book_id) >= :authorsAmount";
        $stmt = $conn->prepare($bookIdsWithNAuthorsSql);
        $result = $stmt->executeQuery(['authorsAmount' => $authorsAmount]);
        $bookIds = array_column($result->fetchAllAssociative(), 'id');

        $entityMapper = new ResultSetMappingBuilder($em, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT);
        $entityMapper->addRootEntityFromClassMetadata(Book::class, 'b');
        $entityMapper->addJoinedEntityFromClassMetadata(Author::class, 'a', 'b', 'authors');

        $booksWithAuthorsSql = "SELECT {$entityMapper->generateSelectClause()}
            FROM book b
            INNER JOIN author_book ab
            ON b.id = ab.book_id
            INNER JOIN author a
            ON ab.author_id = a.id
            WHERE b.id IN (:bookIds)";

        $query = $em->createNativeQuery($booksWithAuthorsSql, $entityMapper);
        $query->setParameter('bookIds', $bookIds);

        return $query->getResult();
    }

    public function findByMinCoAuthorsDql(int $authorsAmount)
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.id')
            ->innerJoin('b.authors', 'a')
            ->groupBy('b.id')
            ->having('COUNT(a.id) >= :authorsAmount')
            ->setParameter('authorsAmount', $authorsAmount);
        
        $bookIds = array_column($qb->getQuery()->execute(), 'id');

        return $this->findMany($bookIds);
    }

    /**
     * @return Book[] Returns an array of Book objects
    */
    public function findMany(array $ids)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function applyFilters(BookFilterDto $bookFilterDTO)
    {
        if($bookFilterDTO->errors) {
            return $bookFilterDTO->errors;
        }

        $qb = $this->createQueryBuilder('b');
        $stringFilters = array_filter([
            'title' => (string) $bookFilterDTO->title,
            'description' => (string) $bookFilterDTO->description,
        ]);
        
        $integerFilters = array_filter([
            'id' => (int) $bookFilterDTO->id,
            'publishYear' => (int) $bookFilterDTO->publishYear
        ]);

        foreach ($stringFilters as $key => $value) {
            $qb->andWhere('b.'. $key . ' LIKE :val')
            ->setParameter('val', '%' . $value . '%');
        }

        foreach ($integerFilters as $key => $value) {
            $qb->andWhere('b.'. $key . ' = :intval')
            ->setParameter('intval', $value);
        }

        if ($author = $bookFilterDTO->author) {
            $qb->leftJoin('b.authors', 'a')
            ->andWhere('a.id = :authorId')
            ->setParameter('authorId', intval($author));
        }

        return $qb->getQuery()
            ->getResult();
    }

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
