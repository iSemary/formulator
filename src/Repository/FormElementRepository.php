<?php

namespace App\Repository;

use App\Entity\FormElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormElement>
 *
 * @method FormElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormElement[]    findAll()
 * @method FormElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormElement::class);
    }

//    /**
//     * @return FormElement[] Returns an array of FormElement objects
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

//    public function findOneBySomeField($value): ?FormElement
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
