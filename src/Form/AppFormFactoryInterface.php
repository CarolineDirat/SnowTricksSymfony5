<?php

namespace App\Form;

use Symfony\Component\Form\FormInterface;

interface AppFormFactoryInterface
{
    public function create(string $name, ?object $entity, array $options = []): ?FormInterface;
}
