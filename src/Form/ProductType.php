<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All; // AJOUTEZ CETTE LIGNE

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('desciption', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Entrez une description du produit',
                ],
            ])
            ->add('stock')
            ->add('price')
            
            // Image principale (mappée à l'entité)
            ->add('image', FileType::class, [
                'label' => 'Image principale',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k', // Augmenté à 2MB
                        'mimeTypes' => ['image/jpg', 'image/png', 'image/jpeg', 'image/webp'],
                        'mimeTypesMessage' => "Format d'image invalide (JPEG, PNG ou WEBP).",
                    ])
                ]
            ])
            
            // Images secondaires (non mappées) - CORRECTION ICI
            ->add('imagesFiles', FileType::class, [
                'label' => 'Images secondaires',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([ // ✅ Utilisez All au lieu de File directement
                        new File([
                            'maxSize' => '2048k',
                            'mimeTypes' => ['image/jpg', 'image/png', 'image/jpeg', 'image/webp'],
                            'mimeTypesMessage' => "Format d'image invalide (JPEG, PNG ou WEBP).",
                        ])
                    ])
                ]
            ])
            
            ->add('subCategories', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}