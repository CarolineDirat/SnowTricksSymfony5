<?php

namespace App\Form;

use App\Entity\Picture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '500k',
                        'maxSizeMessage' => 'Le fichier {{ name }} est trop gros. Il ne doit pas dépasser {{ limit }}.',
                        'notFoundMessage' => "Le fichier {{ file }} n'a pas été trouvé.",
                        'uploadErrorMessage' => "La fichier n'a pas pu être uploadé.",
                    ]),
                    new Image([
                        'minWidth' => 300,
                        'minWidthMessage' => "L'image doit faire au minimum {{ limit }} pixels de largeur",
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => "Le fichier de l'image doit avoir une des extensions suivantes : png, jpeg, jpg, gif, webp.",
                        'minRatio' => 0.67,
                        'minRatioMessage' => "Le ratio {{ ratio }} de l'image (largeur/hauteur) est ici trop petit. Il doit être au moins de {{ max_ratio }}."
                    ]),
                ]
            ])
            ->add('alt', TextType::class, [
                'label' => 'Une brève description de l\'image (facultatif) :',
                'required' => false,
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}
