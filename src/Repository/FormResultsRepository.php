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
class FormResultsRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FormResults::class);
    }

    public function findAllBySessionId(int $sessionId) {
        return $this->createQueryBuilder('fr')
            ->select('fr.field_value as answer', 'ff.title AS question_title', 'ff.type as question_type')
            ->join('App\Entity\Session', 's', 'WITH', 's.id = fr.session_id')
            ->join('App\Entity\FormField', 'ff', 'WITH', 'fr.form_field_id = ff.id')
            ->where('s.id = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->getResult();
    }

    public function findFileByNameAndUserId(string $fileName, int $userId) {
        return $this->createQueryBuilder('fr')
            ->select('fr.field_value')
            ->join('App\Entity\Session', 's', 'WITH', 's.id = fr.session_id')
            ->join('App\Entity\Form', 'f', 'WITH', 's.formId = f.id')
            ->where('fr.field_value = :fieldValue')
            ->setParameter('fieldValue', $fileName)
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
