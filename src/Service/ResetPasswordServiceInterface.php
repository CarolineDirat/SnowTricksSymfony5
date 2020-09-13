<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

/**
 * checkToken and store it in session.
 *
 * @param string $token
 *
 * @return Response|void
 */
interface ResetPasswordServiceInterface
{
    public function changePassword(FormInterface $form, string $token, User $user): void;

    public function sendEmailToResetPassword(User $user, ResetPasswordToken $resetToken): void;
}
