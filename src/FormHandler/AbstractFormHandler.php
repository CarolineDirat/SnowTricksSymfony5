<?php

namespace App\FormHandler;

use Doctrine\Migrations\Query\Exception\InvalidArguments;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFormHandler implements FormHandlerInterface
{
    protected FormInterface $form;

    abstract public function getEntityClass(): string;

    abstract public function process(object $entity): void;

    public function checkEntity(object $entity): object
    {
        $class = $this->getEntityClass();
        if ($entity instanceof $class) {
            return $entity;
        }
        throw new InvalidArguments('Invalid argument in CommentFormHandler::checkEntity()');
    }

    public function handle(Request $request, FormInterface $form, object $entity): bool
    {
        $form->handleRequest($request);
        $this->setForm($form);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->process($this->checkEntity($entity));

            return true;
        }

        return false;
    }

    /**
     * Get the value of form.
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * Set the value of form.
     *
     * @return self
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }
}
