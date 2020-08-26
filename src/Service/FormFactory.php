<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TrickType;
use DateTimeImmutable;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FormFactory
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createCommentForm(Trick $trick, ?User $user): FormInterface
    {
        $comment = new Comment();
        $comment
            ->setTrick($trick)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUser($user);

        return $this->formFactory->create(CommentType::class, $comment);
    }

    public function createTrickForm(): FormInterface
    {
        $trick = new Trick();
        $picture = new Picture();
        $trick->addPicture($picture);
        $video = new Video();
        $trick->addVideo($video);

        return $this->formFactory->create(TrickType::class, $trick);
    }

    public function createUpdateTrickForm(Trick $trick): FormInterface
    {
        return $this->formFactory->create(TrickType::class, $trick);
    }
}
