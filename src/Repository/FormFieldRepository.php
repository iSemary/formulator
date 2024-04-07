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
class FormFieldRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FormField::class);
    }

    public function findByFormId(int $formId): array {
        return $this->createQueryBuilder('fs')
            ->andWhere('fs.form_id = :formId')
            ->setParameter('formId', $formId)
            ->getQuery()
            ->getResult();
    }

    public function findByFormIdAndActive(int $formId): array {
        return $this->createQueryBuilder('fs')
            ->andWhere('fs.form_id = :formId')
            ->setParameter('formId', $formId)
            ->andWhere('fs.status = 1')
            ->getQuery()
            ->getResult();
    }
}
