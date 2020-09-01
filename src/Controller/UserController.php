<?php

namespace App\Controller;

use App\Form\AppFormFactoryInterface;
use App\FormHandler\UserProfileFormHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private AppFormFactoryInterface $appFormFactory;

    public function __construct(AppFormFactoryInterface $appFormFactory)
    {
        $this->appFormFactory = $appFormFactory;
    }

    /**
     * @Route("/compte/profil", name="user_profile")
     *
     * @isGranted("ROLE_USER")
     */
    public function profile(
        Request $request,
        UserProfileFormHandler $userProfileFormHandler
    ): Response {
        $user = $this->getUser();
        $form = $this->appFormFactory->create('up-profile', $user);
        if ($userProfileFormHandler->isHandled($request, $form)) {
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
