<?php

namespace App\Repository;

use App\Entity\FormField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormField>
 *
 * @method FormField|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormField|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormField[]    findAll()
 * @method FormField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormField::class);
    }

//    /**
//     * @return FormField[] Returns an array of FormField objects
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

//    public function findOneBySomeField($value): ?FormField
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
