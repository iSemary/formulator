<?php

namespace App\Repository;

use App\Entity\Session;
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
class SessionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Session::class);
    }

    /**
     * @return int Returns numeric number of total forms for specific user
     */
    public function getTotalSessions(int $userId): int {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->join('App\Entity\Form', 'f', 'WITH', 's.formId = f.id')
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneByIdAndUserId(int $sessionId, int $userId) {
        return $this->createQueryBuilder('s')
            ->select('s.id', 's.formId', 's.ip', 's.agent', 's.created_at')
            ->join('App\Entity\Form', 'f', 'WITH', 's.formId = f.id')
            ->where('f.userId = :userId')
            ->andWhere('s.id = :id')
            ->setParameter('userId', $userId)
            ->setParameter('id', $sessionId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
