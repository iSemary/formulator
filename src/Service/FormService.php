<?php

namespace App\Service;

use App\Entity\Form;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\SecurityBundle\Security;

class FormService {
    private $security;
    private $entityManager;
    private $userId;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->userId = $this->security->getUser()->getUserIdentifier();
    }

    /**
     * @return int The `getTotalForms()` method is returning an integer value, which represents the total
     * number of forms associated with the user ID.
     */
    public function getTotalForms(): int {
        $formRepository = $this->entityManager->getRepository(Form::class);
        return $formRepository->getTotalForms($this->userId);
    }

    /**
     * The function generates a unique hash name using UUID and recursively ensures its uniqueness in a
     * database table.
     * 
     * @return string a unique hash name generated using the `Uuid::uuid4()` method. If the generated hash
     * name already exists in the database, the function recursively calls itself to generate a new unique
     * hash name until a unique one is found.
     */
    public function generateUniqueHashName(): string {
        $hashName = Uuid::uuid4();
        $hashExists = $this->entityManager->getRepository(Form::class)->findByHashName($hashName);
        if ($hashExists) {
            $this->generateUniqueHashName();
        }
        return $hashName;
    }
}
