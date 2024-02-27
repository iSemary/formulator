<?php

namespace App\Repository;

use App\Entity\FormResults;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormResults>
 *
 * @method FormResults|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormResults|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormResults[]    findAll()
 * @method FormResults[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormResultsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormResults::class);
    }

//    /**
//     * @return FormResults[] Returns an array of FormResults objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FormResults
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
