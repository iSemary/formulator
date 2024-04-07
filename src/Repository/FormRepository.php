<?php

namespace App\Repository;

use App\Entity\Form;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Form>
 *
 * @method Form|null find($id, $lockMode = null, $lockVersion = null)
 * @method Form|null findOneBy(array $criteria, array $orderBy = null)
 * @method Form[]    findAll()
 * @method Form[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Form::class);
    }

    /**
     * @return Form[] Returns an array of Form objects
     */
    public function findOneByIdAndUserId(int $id, int $userId) {
        return $this->createQueryBuilder('f')
            ->andWhere('f.id = :id')
            ->setParameter('id', $id)
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return int Returns numeric number of total forms for specific user
     */
    public function getTotalForms(int $userId): int {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneByHashNameAndActive(string $hashName) {
        return $this->createQueryBuilder('f')
            ->andWhere('f.hashName = :hashName')
            ->setParameter('hashName', $hashName)
            ->andWhere('f.status = 1')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
