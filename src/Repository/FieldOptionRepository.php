<?php

namespace App\Repository;

use App\Entity\FieldOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldOption>
 *
 * @method FieldOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldOption[]    findAll()
 * @method FieldOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FieldOptionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FieldOption::class);
    }

    /**
     * Get all options by field ID as an array.
     *
     * @param int $fieldId
     * @return array
     */
    public function findAllByFieldIdAsArray(int $fieldId): array {
        return  $this->createQueryBuilder('fo')
            ->select('fo.id, fo.option_value')
            ->where('fo.field_id = :fieldId')
            ->setParameter('fieldId', $fieldId)
            ->andWhere('fo.status = 1')
            ->getQuery()
            ->getArrayResult();
    }
}
