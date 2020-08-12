<?php

namespace App\FormHandler;

use App\Entity\Comment;

class CommentFormHandler extends AbstractFormHandler
{
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
}
