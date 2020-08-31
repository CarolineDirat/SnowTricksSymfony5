<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;

interface TrickUpdateFormServiceInterface
{
    public function process(FormInterface $form): void;

    public function errorsHandler(FormInterface $form): FormInterface;
}
