<?php

namespace App\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserExtension extends AbstractExtension {
    private $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('get_current_user', [$this, 'getCurrentUser']),
        ];
    }

    public function getCurrentUser(): ?\App\Entity\User {
        return $this->security->getUser();
    }
}
