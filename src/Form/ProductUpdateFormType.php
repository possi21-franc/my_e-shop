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

class ProductUpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('desciption' , TextareaType::class,[
                 'attr' => [
        'class' => 'form-control',
        'rows' => 3, // nombre de lignes par défaut
        'placeholder' => 'Entrez une description du produit',
    ],
            ])
            ->add('price')
 ->add('image',FileType::class, [
                'label' =>'image de produit',
                'mapped'=> false,
                'required'=> false,
                'constraints' => [
                    new File([
                        "maxSize"=>'1024k',
                        "mimeTypes"=>[
                            'image/jpg',
                            'image/png',
                            'image/jpeg'


                        ],
                           'mimeTypesMessage'=>"votre image de produit doit être au format vailde  (JPEG ou PNG)"
                    ])
                ]
                
            ])            //->add('stock')
            ->add('subCategories', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
