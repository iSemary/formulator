<?php

namespace App\Service;

use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SessionService {
    private $security;
    private $entityManager;
    private $userId;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->userId = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null;
    }


    public function getTotalSessions(): int {
        $sessionRepository = $this->entityManager->getRepository(Session::class);
        return $sessionRepository->getTotalSessions($this->userId);
    }
}
