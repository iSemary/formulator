<?php

namespace App\Repository;

use App\Entity\Sessions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sessions>
 *
 * @method Sessions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sessions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sessions[]    findAll()
 * @method Sessions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sessions::class);
    }

//    /**
//     * @return Sessions[] Returns an array of Sessions objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sessions
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
