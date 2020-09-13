<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AppFormFactoryInterface;
use App\FormHandler\RegistrationFormHandler;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    private AppFormFactoryInterface $appFormFactory;

    public function __construct(
        EmailVerifier $emailVerifier,
        AppFormFactoryInterface $appFormFactory
    ) {
        $this->emailVerifier = $emailVerifier;
        $this->appFormFactory = $appFormFactory;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        RegistrationFormHandler $registrationFormHandler
    ): Response {
        $user = new User();
        $form = $this->appFormFactory->create('registration', $user);
        if ($registrationFormHandler->isHandled($request, $form)) {
            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }
        $this->addFlash(
            'success',
            'Votre addresse mail a validée, et votre compte est activé.'
        );

        return $this->redirectToRoute('tricks');
    }
}
