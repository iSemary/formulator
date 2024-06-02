<?php

namespace App\Service;

use App\Entity\Form;
use App\Entity\FormField;
use App\Entity\FormResults;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FormSubmission {
    private $entityManager;
    private $filePath;

    public function __construct(EntityManagerInterface $entityManager, string $filePath) {
        $this->entityManager = $entityManager;
        $this->filePath = $filePath;
    }

    public function submit(string $hashName, Request $request) {
        $form = $this->entityManager->getRepository(Form::class)->findOneByHashNameAndActive($hashName);

        if (!$form) {
            throw new \Exception('Form not found or inactive.');
        }

        $formData = $request->request->all();

        $formId = $form->getId();
        $formFields = $this->entityManager->getRepository(FormField::class)->findByFormIdAndActive($formId);

        // Initialize session
        $sessionId = $this->initSession($form->getId(), $request);

        foreach ($formFields as $formField) {
            $fieldName = $formField->getName();
            if (isset($formData[$fieldName])) {
                $fieldValue = $formData[$fieldName];
                if (is_array($fieldValue) && $formField->getType() == 4) { // TODO avoid magic numbers
                    foreach ($fieldValue as $value) {
                        $this->saveResult($formId, $sessionId, $formField->getId(), $value);
                    }
                } else {
                    $this->saveResult($formId, $sessionId, $formField->getId(), $fieldValue);
                }
            } elseif ($request->files->has($fieldName)) {
                $fileName = $this->uploadFile($request->files->get($fieldName));
                $this->saveResult($formId, $sessionId, $formField->getId(), $fileName);
            } else {
                if ($formField->getRequired()) {
                    throw new \Exception("Field $fieldName is missing in the request.");
                }
            }
        }
    }

    /**
     * The function `saveResult` saves form submission data to the database using an entity manager in PHP.
     * 
     * @param int formId The `formId` parameter represents the ID of the form for which the result is being
     * saved. This helps in associating the form submission with the specific form in the database.
     * @param int sessionId The `sessionId` parameter in the `saveResult` function represents the unique
     * identifier for the session during which the form submission occurred. It is used to associate the
     * form submission with a specific session, allowing for tracking and analysis of form submissions
     * based on the session context.
     * @param int fieldId The `fieldId` parameter in the `saveResult` function represents the ID of the
     * form field for which the value is being saved. It is used to identify the specific field within a
     * form submission.
     * @param string fieldValue The `fieldValue` parameter in the `saveResult` function represents the
     * value that is submitted for a specific form field. This value will be saved in the database as part
     * of the form submission record. It is a string type parameter, meaning it can store textual data such
     * as user input, selections
     */
    private function saveResult(int $formId, int $sessionId, int $fieldId, string $fieldValue): void {
        $formSubmission = new FormResults();

        $formSubmission->setFormId($formId);
        $formSubmission->setSessionId($sessionId);
        $formSubmission->setFormFieldId($fieldId);
        $formSubmission->setFieldValue($fieldValue);

        $this->entityManager->persist($formSubmission);
        $this->entityManager->flush();
    }
    /**
     * The `initSession` function initializes a new session with form ID, client IP, user agent, and
     * creation timestamp, and persists it in the database.
     * 
     * @param int formId The `formId` parameter in the `initSession` function is an integer value that
     * represents the ID of a form. This function initializes a new session by creating a `Session` entity,
     * setting its properties such as `formId`, `ip`, `agent`, and `createdAt`, persisting
     * @param Request request The `initSession` function takes two parameters:
     * 
     * @return int The `initSession` function returns the ID of the newly created session after persisting
     * it in the database.
     */
    private function initSession(int $formId, Request $request): int {
        $ip = $request->getClientIp();
        $agent = $request->headers->get('User-Agent');
        $createdAt = new \DateTimeImmutable();

        $session = new Session();
        $session->setFormId($formId);
        $session->setIp($ip);
        $session->setAgent($agent);
        $session->setCreatedAt($createdAt);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session->getId();
    }

    /**
     * The `uploadFile` function in PHP generates a unique filename, creates a directory if it doesn't
     * exist, and moves an uploaded file to that directory.
     * 
     * @param UploadedFile file UploadedFile 
     * 
     * @return string The `uploadFile` function returns the file name of the uploaded file after moving it
     * to the specified directory.
     */
    private function uploadFile(UploadedFile $file): string {
        $hashName = Uuid::uuid4();

        // Ensure the directory exists
        // Then Create the directory
        if (!file_exists($this->filePath)) {
            mkdir($this->filePath, 0755, true);
        }

        $fileName = $hashName . '.' . $file->guessExtension();
        $file->move($this->filePath, $fileName);

        return $fileName;
    }
}
