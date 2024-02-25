<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

use function Symfony\Component\Clock\now;

class AuthController extends AbstractController {
    #[Route('/login', name: 'app_login')]
    private $entityManager;
    private $tokenStorage;
    private $eventDispatcher;
    private $emailVerifier;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        EmailVerifier $emailVerifier,
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['email' => $form->get("email")->getData()]);

            if (!$user) {
                // User not found, handle this case (e.g., display an error message)
                $this->addFlash('error', 'Invalid email or password.');
                return $this->redirectToRoute('app_login');
            }

            // Check if the provided password matches the user's password
            if (!$userPasswordHasher->isPasswordValid($user, $form->get("password")->getData())) {
                // Password doesn't match, handle this case
                $this->addFlash('error', 'Invalid email or password.');
                return $this->redirectToRoute('app_login');
            }

            // Authenticate user after login
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);

            // Fire the login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($event);

            // Redirect the user to dashboard
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('auth/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Check if a user with this email already exists
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData()]);

            if ($existingUser) {
                $this->addFlash('error', 'There is already an account with this email. Please use a different email address.');
                return $this->redirectToRoute('app_register');
            }

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setCreatedAt(now());
            $user->setUpdatedAt(now());

            $entityManager->persist($user);
            $entityManager->flush();


            // Authenticate user after registration
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);

            // Fire the login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($event);

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('abdelrahmansamirmostafa@gmail.com', 'Formulator App'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
