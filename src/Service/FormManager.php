<?php

namespace App\Service;

use App\Entity\Form;
use App\Service\FormManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\Clock\now;

class FormManager implements FormManagerInterface {
    private $security;
    private $entityManager;
    private $formSettingManager;
    private $formFieldManager;
    private $formService;
    private $userId;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        FormSettingManager $formSettingManager,
        FormFieldManager $formFieldManager,
        FormService $formService,
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->formSettingManager = $formSettingManager;
        $this->formFieldManager = $formFieldManager;
        $this->formService = $formService;
        $this->userId = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null;
    }

    /**
     * The function creates a new form entity with details, settings, and fields provided in the request
     * data.
     * 
     * @param Request request  is an object that represents the HTTP request made to the server. It
     * contains information such as request headers, parameters, and body content. In this context, the
     *  parameter is being used to retrieve data sent from a form submission.
     * 
     */
    public function createForm(Request $request): Form {
        $hashName = $this->formService->generateUniqueHashName();

        $form = new Form();
        $form->setUserId($this->userId);
        $form->setTitle($request->get('details')['detail_title']);
        $form->setDescription($request->get('details')['detail_description']);
        $form->setHashName($hashName);
        $form->setCreatedAt(now());
        $form->setUpdatedAt(now());
        $form->setStatus(1);
        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $formId = $form->getId();
        // Save Form Settings
        $requestSettings = $request->get('settings');
        $this->formSettingManager->create($formId, $requestSettings);
        // Save Form Fields
        $fields = $request->get('fields');
        $this->formFieldManager->create($formId, $fields);

        return $form;
    }

    /**
     * The function `updateForm` updates a form entity with the provided ID based on the request data.
     * 
     * @param int id The `id` parameter in the `updateForm` function is an integer that represents the
     * unique identifier of the form that needs to be updated. This identifier is used to retrieve the
     * specific form from the database for updating its details.
     * @param Request request The `updateForm` function takes two parameters: an integer `` and a
     * `Request` object ``. The `` object typically contains data sent by the client in an
     * HTTP request. In this case, it seems like the `` object is expected to have 'settings'
     * 
     * @return Form The `updateForm` function returns an instance of the `Form` entity if a form with the
     * specified ID and user ID is found and updated successfully. If the form is not found, it returns a
     * new `JsonResponse` with a message indicating that the update was not successful.
     */
    public function updateForm(int $id, Request $request): Form {
        $form = $this->entityManager->getRepository(Form::class)->findOneByIdAndUserId($id, $this->userId);
        if ($form) {
            $this->updateFormRow($form, $request);

            $formId = $form->getId();
            // Update Form Settings
            $requestSettings = $request->get('settings');
            $this->formSettingManager->update($formId, $requestSettings);
            // Update Form Fields
            $fields = $request->get('fields');
            $this->formFieldManager->update($formId, $fields);

            return $form;
        }
        return new JsonResponse(['success' => false], 400);
    }

    /**
     * This PHP function deletes a form entity based on the provided ID and user ID.
     * 
     * @param int id The `id` parameter in the `deleteForm` function is of type `int` and represents the
     * unique identifier of the form that needs to be deleted.
     * 
     * @return JsonResponse A `JsonResponse` object with a success message indicating the deletion was
     * successful.
     */
    public function deleteForm(int $id): JsonResponse {
        $form = $this->entityManager->getRepository(Form::class)->findOneByIdAndUserId($id, $this->userId);
        if ($form) {
            $form->setStatus(0);
            $this->entityManager->persist($form);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true], 200);
        }
        return new JsonResponse(['success' => true], 200);
    }

    /**
     * The function `restoreForm` restores a form by setting its status to 1 in a PHP application.
     * 
     * @param int id The `id` parameter in the `restoreForm` function is used to specify the unique
     * identifier of the form that needs to be restored. This identifier is used to retrieve the form
     * entity from the database based on the provided ID.
     * 
     * @return JsonResponse The `restoreForm` function is returning a `JsonResponse` with a success message
     * if the form is found and its status is updated successfully. If the form is not found, it still
     * returns a `JsonResponse` with a success message.
     */
    public function restoreForm(int $id): JsonResponse {
        $form = $this->entityManager->getRepository(Form::class)->findOneByIdAndUserId($id, $this->userId);
        if ($form) {
            $form->setStatus(1);
            $this->entityManager->persist($form);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true], 200);
        }
        return new JsonResponse(['success' => true], 200);
    }

    public function getAllForms(Request $request, DataTableFactory $dataTableFactory) {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, ['label' => "ID"])
            ->add('title', TextColumn::class, ['label' => "Title"])
            ->add('status', TextColumn::class, ['label' => "Status", 'render' => function ($value) {
                return ($value == 1 ? "Active " : "In Active");
            }])
            ->add('created_at', DateTimeColumn::class, ['label' => "Created At"])
            ->add('actions', TextColumn::class, ['label' => "Actions", 'render' => function ($value, $row) {
                $buttons = sprintf('<a href="/dashboard/results?form_id=%u" class="btn btn-sm btn-info me-1">Results</a>', $row->getId());
                $buttons .= sprintf('<a href="/dashboard/forms/edit/%u" class="btn btn-sm btn-primary me-1">Edit</a>', $row->getId());
                $buttons .= sprintf('<button data-id="%u" class="btn btn-sm btn-' . ($row->getStatus() == 1 ? "danger delete-form" : "warning restore-form") . '" type="button">' . ($row->getStatus() == 1 ? "Delete" : "Restore") . '</button>', $row->getId());
                return $buttons;
            }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Form::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('f')
                        ->from(Form::class, 'f')
                        ->where('f.userId = :userId')
                        ->orderBy("DESC")
                        ->orderBy('f.id', 'DESC')
                        ->setParameter('userId', $this->userId);
                },
            ])
            ->handleRequest($request);

        return $table;
    }

    public function updateFormRow($form, $request) {
        $form->setTitle($request->get('details')['detail_title']);
        $form->setDescription($request->get('details')['detail_description']);
        $form->setUpdatedAt(now());
        $this->entityManager->persist($form);
        $this->entityManager->flush();
    }
}
