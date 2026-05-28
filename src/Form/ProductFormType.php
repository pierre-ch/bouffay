<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.ad_name',
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.description',
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'form.price',
                'currency' => 'EUR',
                'constraints' => [new NotBlank(), new Positive()],
            ])
            ->add('stock', NumberType::class, [
                'label' => 'form.available_quantity',
                'html5' => true,
                'constraints' => [new NotBlank(), new Positive()],
            ])
            ->add('weight', NumberType::class, [
                'label' => 'form.weight_kg',
                'required' => false,
                'html5' => true,
            ])
            ->add('origin', TextType::class, [
                'label' => 'form.origin',
                'required' => false,
            ])
            ->add('expiresAt', DateType::class, [
                'label' => 'form.expiration_date',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'form.status',
                'choices' => [
                    'form.status_active' => 'active',
                    'form.status_inactive' => 'inactive',
                    'form.status_sold' => 'sold',
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'form.category',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'form.choose_category',
                'constraints' => [new NotBlank()],
            ])
            ->add('tags', EntityType::class, [
                'label' => 'form.tags',
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['size' => 6],
            ])
            ->add('imageFiles', FileType::class, [
                'label' => 'form.photos',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                            'mimeTypesMessage' => 'form.error.invalid_image_format',
                        ]),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
