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
class FormSettingRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FormSetting::class);
    }

    public function findByFormId(int $formId): array {
        return $this->createQueryBuilder('fs')
            ->andWhere('fs.form_id = :formId')
            ->setParameter('formId', $formId)
            ->getQuery()
            ->getResult();
    }

    public function findOneByFormIdAndKey(int $formId, string $key) {
        return $this->createQueryBuilder('fs')
            ->andWhere('fs.form_id = :formId')
            ->setParameter('formId', $formId)
            ->andWhere('fs.setting_key = :settingKey')
            ->setParameter('settingKey', $key)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
