<?php

namespace App\FormHandler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface FormHandlerInterface
{
    public function getEntityClass(): string;

    public function process(object $entity): void;

    public function checkEntity(object $entity): object;

    public function isHandled(Request $request, FormInterface $form): bool;
}
