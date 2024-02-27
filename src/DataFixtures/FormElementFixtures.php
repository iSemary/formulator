<?php

namespace App\DataFixtures;

use App\Entity\FormElement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FormElementFixtures extends Fixture {
    public function load(ObjectManager $manager): void {
        $formData = [
            ['title' => 'Short Text', 'type' => 1, 'icon' => 'fas fa-grip-lines'],
            ['title' => 'Paragraph', 'type' => 2, 'icon' => 'fas fa-align-justify'],
            ['title' => 'Single Choice', 'type' => 3, 'icon' => 'fas fa-dot-circle'],
            ['title' => 'Multiple Choice', 'type' => 4, 'icon' => 'far fa-check-square'],
            ['title' => 'File Upload', 'type' => 5, 'icon' => 'fas fa-cloud-upload-alt'],
            ['title' => 'Date', 'type' => 6, 'icon' => 'fas fa-calendar-alt'],
            ['title' => 'Time', 'type' => 7, 'icon' => 'far fa-clock'],
        ];

        foreach ($formData as $data) {
            $formElement = new FormElement();
            $formElement->setTitle($data['title']);
            $formElement->setType($data['type']);
            $formElement->setIcon($data['icon']);
            $formElement->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($formElement);
        }

        $manager->flush();
    }
}
