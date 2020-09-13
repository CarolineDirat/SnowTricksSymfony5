<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AppFormFactoryInterface;
use App\Service\ResetPasswordServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private ResetPasswordHelperInterface $resetPasswordHelper;
    private AppFormFactoryInterface $appFormFactory;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        AppFormFactoryInterface $appFormFactory
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->appFormFactory = $appFormFactory;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request, ResetPasswordServiceInterface $resetPasswordService): Response
    {
        $form = $this->appFormFactory->create('reset-password-request', null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $resetPasswordService
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // We prevent users from directly accessing this page
        if (!$this->canCheckEmail()) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="app_reset_password")
     */
    public function reset(
        Request $request,
        string $token = null,
        ResetPasswordServiceInterface $resetPasswordService
    ): Response {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'Il y a eut un problème validation de votre demande de réinitilaisation - %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->appFormFactory->create('change-password', null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resetPasswordService->changePassword($form, $token, $user);
            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $this->addFlash(
                'success',
                'La réinitialisation de votre mot de passe a réussi. Vous pouvez maintenant vous connecter.'
            );

            return $this->redirectToRoute('home');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(
        string $emailFormData,
        ResetPasswordServiceInterface $resetPasswordService
    ): RedirectResponse {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            $this->addFlash('reset_password_error', 'Cet email n\'existe pas dans la base de données');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Marks that you are allowed to see the app_check_email page.
        $this->setCanCheckEmailInSession();

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // To tell the user why a reset email was not sent.
            // Caution: This may reveal if a user is registered or not.
            $this->addFlash('reset_password_error', sprintf(
                'Echec de la demande de réinitialisation de mot de passe - %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $resetPasswordService->sendEmailToResetPassword($user, $resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
