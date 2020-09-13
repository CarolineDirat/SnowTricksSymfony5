<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordService implements ResetPasswordServiceInterface
{
    use ResetPasswordControllerTrait;

    private Session $session;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ManagerRegistry $manager;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private MailerInterface $mailer;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        ManagerRegistry $manager,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer
    ) {
        $this->session = new Session(new NativeSessionStorage(), new AttributeBag());
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->mailer = $mailer;
    }

    public function changePassword(FormInterface $form, string $token, User $user): void
    {
        // A password reset token should be used only once, remove it.
        $this->resetPasswordHelper->removeResetRequest($token);

        // Encode the plain password, and set it.
        $encodedPassword = $this->passwordEncoder->encodePassword(
            $user,
            $form->get('plainPassword')->getData()
        );

        $user->setPassword($encodedPassword);
        $this->manager->getManager()->flush();
    }

    public function sendEmailToResetPassword(User $user, ResetPasswordToken $resetToken): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('mailer@snowtricks.test', 'Snowtricks'))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe sur Snowtricks')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
            ])
        ;

        $this->mailer->send($email);
    }
}
