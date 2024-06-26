<?php

namespace App\Controller;

use App\Entity\FieldOption;
use App\Entity\Form;
use App\Entity\FormElement;
use App\Entity\FormField;
use App\Entity\FormSetting;
use App\Service\FormManager;
use App\Service\FormSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController {
    private $entityManager;
    private $security;
    private $formElements;
    private $formManager;
    private $formSubmission;
    private $userId;

    public function __construct(EntityManagerInterface $entityManager, Security $security, FormManager $formManager, FormSubmission $formSubmission) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->formManager = $formManager;
        $this->formSubmission = $formSubmission;
        $this->formElements = $this->entityManager->getRepository(FormElement::class)->findAll();
        $this->userId = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null;
    }

    #[Route('dashboard/forms', name: 'app_forms')]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response {
        // return forms as dataTable
        $table = $this->formManager->getAllForms($request, $dataTableFactory);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('dashboard/forms/index.html.twig', ['datatable' => $table]);
    }

    #[Route('dashboard/forms/create', name: 'app_forms_create')]
    public function create(): Response {
        return $this->render('dashboard/forms/editor.html.twig', ['elements' => $this->formElements]);
    }

    /**
     * The function stores a new form with its details, settings, and fields in a PHP application.
     * 
     * @param Request request The code snippet you provided is a PHP function that handles the storing of
     * form data in a database. It takes a Request object as a parameter, which likely contains the data
     * submitted from a form.
     * 
     * @return JsonResponse A JSON response with status code 200 is being returned.
     */
    #[Route('dashboard/forms/store', name: 'app_forms_store')]
    public function store(Request $request): JsonResponse {
        // Create Form
        $form = $this->formManager->createForm($request);
        return new JsonResponse(["status" => 201, 'id' => $form->getId()], 200);
    }

    #[Route('dashboard/forms/edit/{id}', name: 'app_forms_edit')]
    public function edit($id): Response {
        $form = $this->entityManager->getRepository(Form::class)->findOneByIdAndUserId($id, $this->userId);
        if (!$form) {
            throw new NotFoundHttpException('Form not authorized or does not exist.');
        }
        $form->settings = $this->prepareFormSettings($id);
        return $this->render('dashboard/forms/editor.html.twig', ['elements' => $this->formElements, 'form' => $form]);
    }

    #[Route('dashboard/forms/update/{id}', name: 'app_forms_update')]
    public function update(int $id, Request $request): Response {
        $form = $this->entityManager->getRepository(Form::class)->findOneByIdAndUserId($id, $this->userId);
        if (!$form) {
            throw new NotFoundHttpException('Form not authorized or does not exist.');
        }
        $this->formManager->updateForm($id, $request);

        return new JsonResponse(["status" => 200, 'id' => $id], 200);
    }

    #[Route('/dashboard/forms/{id}', name: 'app_forms_details')]
    public function returnFormDetails(int $id): JsonResponse {
        $form = $this->prepareFormForModify($id, $this->userId);
        return new JsonResponse(['form' => $form], 200);
    }

    #[Route('/forms/{hashName}', name: 'app_forms_show')]
    public function show(string $hashName) {
        $form = $this->prepareFormForSubmit($hashName);
        return $this->render('forms/index.html.twig', ['form' => $form]);
    }

    #[Route('/forms/{hashName}/submit', name: 'app_forms_submit', methods: ['POST'])]
    public function submit(Request $request, $hashName): Response {
        $form = $this->entityManager->getRepository(Form::class)->findOneByHashNameAndActive($hashName);
        if (!$form) {
            throw new NotFoundHttpException('Form not authorized or does not exist.');
        }
        // Submit the form
        $this->formSubmission->submit($hashName, $request);

        // Check form redirection setting 
        return $this->redirectAfterSubmission($form);
    }

    #[Route('dashboard/forms/delete/{id}', name: 'app_forms_delete')]
    public function delete($id): JsonResponse {
        return $this->formManager->deleteForm($id);
    }

    #[Route('dashboard/forms/restore/{id}', name: 'app_forms_restore')]
    public function restore($id): JsonResponse {
        return $this->formManager->restoreForm($id);
    }

    /**
     * The function prepareFormForModify retrieves form details, settings, and fields for a given form ID.
     * 
     * @param int id It looks like you were about to provide information about the `id` parameter in the
     * `prepareFormForModify` function, but the value is missing. Could you please provide the value of the
     * `id` parameter so I can assist you further?
     * 
     * @return array An array is being returned with three keys: 'details', 'settings', and 'fields'. The
     * 'details' key contains the form entity retrieved by its ID, the 'settings' key contains the form
     * settings prepared for the given ID, and the 'fields' key contains the form fields prepared for the
     * given ID.
     */
    private function prepareFormForModify(int $id): array {
        $form['fields'] = $this->prepareFormFields($id);
        return $form;
    }

    /**
     * The function prepares form details, settings, and fields for submission based on a given hash name.
     * 
     * @param string hashName It looks like you are working on a function `prepareFormForSubmit` that
     * prepares a form for submission. The function takes a parameter `` of type string.
     * 
     * @return array An array containing details, settings, and fields of a form is being returned.
     */
    private function prepareFormForSubmit(string $hashName): array {
        $form = $this->entityManager->getRepository(Form::class)->findOneByHashNameAndActive($hashName);
        // Check if the form exists
        if (!$form) {
            throw new NotFoundHttpException('Form not authorized or does not exist.');
        }

        $data['details'] = $form;
        $data['settings'] = $this->prepareFormSettings($data['details']->getId());
        // Check if the form expire date has been passed
        $currentDate = new \DateTime();
        if (isset($data['settings']['setting_expire_at']) && $currentDate > \DateTime::createFromFormat('Y-m-d', $data['settings']['setting_expire_at'])) {
            throw new NotFoundHttpException('Form has been expired or not available right now.');
        }
        $data['fields'] = $this->prepareFormFields($data['details']->getId());
        return $data;
    }

    /**
     * The function `prepareFormFields` retrieves and formats form fields based on a given form ID.
     * 
     * @param int id The `prepareFormFields` function takes an integer parameter `` which represents the
     * form ID. This function retrieves form fields associated with the given form ID from the database and
     * formats them into an array with specific keys such as 'id', 'title', 'description', 'type',
     * 'required',
     * 
     * @return array An array of formatted form fields is being returned. Each element in the array
     * contains the following keys: 'id', 'title', 'description', 'type', 'required', and 'order_number',
     * with corresponding values extracted from the FormField entities fetched from the database based on
     * the provided form ID.
     */
    private function prepareFormFields(int $id): array {
        $formattedFields = [];
        $fields = $this->entityManager->getRepository(FormField::class)->findByFormIdAndActive($id);
        foreach ($fields as $key => $field) {
            $formattedFields[$key]['id'] = $field->getId();
            $formattedFields[$key]['title'] = $field->getTitle();
            $formattedFields[$key]['description'] = $field->getDescription();
            $formattedFields[$key]['type'] = $field->getType();
            $formattedFields[$key]['required'] = $field->getRequired();
            $formattedFields[$key]['order_number'] = $field->getOrderNumber();
            $formattedFields[$key]['options'] = $this->getFieldOptions($field->getId());
        }
        return $formattedFields;
    }

    /**
     * The function `prepareFormSettings` retrieves and formats form settings based on the provided form
     * ID.
     * 
     * @param int id It looks like you are trying to prepare form settings by fetching them from the
     * database based on the provided form ID. The `prepareFormSettings` function takes an integer ID as a
     * parameter and returns an array of formatted settings.
     * 
     * @return array An array of formatted form settings is being returned from the `prepareFormSettings`
     * function. The settings are retrieved from the database based on the provided form ID, and then
     * formatted into an associative array where the setting key is the array key and the setting value is
     * the array value.
     */
    private function prepareFormSettings(int $id): array {
        $formattedSettings = [];
        $settings = $this->entityManager->getRepository(FormSetting::class)->findByFormId($id);
        foreach ($settings as $setting) {
            $formattedSettings[$setting->getSettingKey()] = $setting->getSettingValue();
        }
        return $formattedSettings;
    }

    /**
     * This PHP function retrieves field options from the database based on a given field ID.
     * 
     * @param int fieldId Thank you for providing the code snippet. It seems like you are trying to
     * retrieve field options based on a given field ID. If you have any specific field ID in mind, please
     * provide me with the value of the `fieldId` parameter so that I can assist you further in
     * understanding how the `
     * 
     * @return array An array of field options associated with the given field ID is being returned.
     */
    private function getFieldOptions(int $fieldId): array {
        $options = $this->entityManager->getRepository(FieldOption::class)->findAllByFieldIdAsArray($fieldId);
        return $options;
    }

    /**
     * The function `redirectAfterSubmission` checks for a redirect setting and returns a RedirectResponse
     * if found, otherwise renders a success template.
     * 
     * @param form Based on the code snippet you provided, the `redirectAfterSubmission` function takes a
     * `` parameter. This function retrieves a redirect option from the database based on the form ID
     * and a specific key ("setting_redirect_to"). If a redirect option is found, it returns a
     * `RedirectResponse` to
     * 
     * @return Response If the `` is found, a `RedirectResponse` with the URL specified in
     * the setting value will be returned. Otherwise, it will render the 'forms/success.html.twig'
     * template.
     */
    private function redirectAfterSubmission($form): Response {
        $redirectOption = $this->entityManager->getRepository(FormSetting::class)->findOneByFormIdAndKey($form->getId(), "setting_redirect_to");
        if ($redirectOption) {
            return new RedirectResponse($redirectOption->getSettingValue());
        }
        return $this->render('forms/success.html.twig');
    }
}
