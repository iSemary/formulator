<?php

namespace App\Repository;

use App\Entity\FormSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormSetting>
 *
 * @method FormSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormSetting[]    findAll()
 * @method FormSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSetting::class);
    }

//    /**
//     * @return FormSetting[] Returns an array of FormSetting objects
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

//    public function findOneBySomeField($value): ?FormSetting
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
