<?php

namespace App\FormHandler;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationFormHandler extends AbstractFormHandler
{
    private ManagerRegistry $manager;

    private UserPasswordEncoderInterface $passwordEncoder;

    private EmailVerifier $emailVerifier;

    private Session $session;

    public function __construct(
        ManagerRegistry $manager,
        UserPasswordEncoderInterface $passwordEncoder,
        EmailVerifier $emailVerifier
    ) {
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
        $this->emailVerifier = $emailVerifier;
        $this->session = new Session(new NativeSessionStorage(), new AttributeBag());
    }

    public function getEntityClass(): string
    {
        return User::class;
    }

    public function process(object $user): void
    {
        // encode the plain password
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $this->form->get('plainPassword')->getData()
            )
        );

        $entityManager = $this->manager->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->sendEmailConfirmation($user);

        // add a flash message to notify confirmation success
        $this->session->getFlashBag()->add('success', "Votre compte a été créé. 
            Il reste à l'activer depuis le mail de confirmation que nous vous avons envoyé.");
    }

    /**
     * sendEmailConfirmation : generate a signed url and email it to the user.
     *
     * @return void
     */
    public function sendEmailConfirmation(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('mailer@snowtricks.test', 'Snowtricks'))
                ->to($user->getEmail())
                ->subject('Confirmation de votre Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
