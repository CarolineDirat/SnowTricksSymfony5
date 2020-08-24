<?php

namespace App\FormHandler;

use App\Entity\Comment;
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
