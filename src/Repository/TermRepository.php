<?php

namespace App\Repository;

use App\Entity\Term;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Term|null find($id, $lockMode = null, $lockVersion = null)
 * @method Term|null findOneBy(array $criteria, array $orderBy = null)
 * @method Term[]    findAll()
 * @method Term[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TermRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Term::class);
    }

    /**
     * @param $idTag
     * @return Query
     */
    public function findByQuery($idTag): Query
    {
        $query = $this->createQueryBuilder('term')
            ->where('term.tag = :idTag')
            ->orderBy('term.word')
            ->setParameter('idTag', $idTag);
        return $query->getQuery();
    }

    /**
     * @return Term[] Returns an array of Term objects
     */
    public function findByTagId($tagId)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.tag = :val')
            ->setParameter('val', $tagId)
            ->orderBy('t.word')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Term[] Returns an array of Term objects
     */
    public function findForTest($tagId, $length)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.tag = :val')
            ->setParameter('val', $tagId)
            ->setMaxResults($length)
            ->orderBy('RAND()')
            ->getQuery()
            ->getResult()
            ;
    }

    /*
    public function findOneBySomeField($value): ?Term
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
