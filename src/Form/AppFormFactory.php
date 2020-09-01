<?php

namespace App\Form;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class AppFormFactory implements AppFormFactoryInterface
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function create(string $name, object $entity, array $options = []): ?FormInterface
    {
        $formTypes = [
            'ad-comment' => CommentType::class,
            'ad-trick' => TrickType::class,
            'up-trick' => TrickType::class,
            'up-profile' => ProfileType::class,
        ];

        $formType = $formTypes[$name];

        if (!empty($formType)) {
            return $this->formFactory->create($formType, $entity, $options);
        }

        return null;
    }
}
