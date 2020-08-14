<?php

namespace App\FormHandler;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;

class CommentFormHandler extends AbstractFormHandler
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function getEntityClass(): string
    {
        return Comment::class;
    }

    public function initialize(Trick $trick, ?User $user): Comment
    {
        $comment = new Comment();

        return $comment
            ->setTrick($trick)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUser($user);
    }

    public function process(object $entity): void
    {
        $entityManager = $this->getManagerRegistry()->getManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }
}
