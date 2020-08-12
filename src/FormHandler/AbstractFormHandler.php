<?php

namespace App\FormHandler;

use Doctrine\Migrations\Query\Exception\InvalidArguments;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFormHandler implements FormHandlerInterface
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    abstract public function getEntityClass(): string;

    abstract public function process(object $entity): void;

    public function checkEntity(object $entity): object
    {
        $class = $this->getEntityClass();
        if ($entity instanceof $class) {
            return $entity;
        }
        throw new InvalidArguments("Invalid argument in CommentFormHandler::checkEntity()");
    }

    public function handle(Request $request, FormInterface $form, object $entity): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->process($this->checkEntity($entity));

            return true;
        }
        return false;
    }

    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }
}
