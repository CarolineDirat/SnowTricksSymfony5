<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Modifier ma photo de profil (max 10Mo) :',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSizeMessage' => 'Le fichier {{ name }} est trop gros. 
                            Il ne doit pas dépasser {{ limit }} {{ suffix }}.',
                        'notFoundMessage' => "Le fichier {{ file }} n'a pas été trouvé.",
                        'uploadErrorMessage' => "La fichier n'a pas pu être uploadé.",
                        'minWidth' => 100,
                        'minWidthMessage' => "L'image doit faire au minimum {{ min_width }} pixels de largeur",
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => "Le fichier de l'image doit avoir une des extensions suivantes : 
                            png, jpeg, jpg, gif, webp.",
                        'minRatio' => 0.67,
                        'minRatioMessage' => "Le ratio {{ ratio }} de l'image (largeur/hauteur) est ici trop petit. 
                            Il doit être au moins de {{ min_ratio }}.",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
