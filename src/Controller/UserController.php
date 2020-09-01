<?php

namespace App\Controller;

use App\Form\AppFormFactoryInterface;
use App\Service\ImageProcessInterface;
use App\Service\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function profile(Request $request, ImageProcessInterface $imageProcess, UserServiceInterface $userService): Response
    {
        $user = $this->getUser();
        $form = $this->appFormFactory->create('up-profile', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file instanceof UploadedFile) {
                // define filename without extension
                $filename = base_convert($user->getUuid()->getHex(), 16, 30);
                dump($filename);
                // Resize the picture file, move it in it's directory
                // and save it's name in profile user property
                try {
                    $fullFilename = $imageProcess->executeForProfile($file, $filename);
                    if (empty($user->getProfile())) {
                        $user->setProfile($fullFilename);
                    }
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload or process
                    $this->addFlash('upload', $e->getMessage());
                }
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', "Votre photo de profil vient d'être modifiée");

                return $this->redirectToRoute('user_profile');
            }

            $this->addFlash('notice', "L'upload du fichier a échoué");

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
