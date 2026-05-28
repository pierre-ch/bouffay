<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'    => 'form.name',
                'required' => false,
                'attr'     => ['placeholder' => 'form.search.placeholder'],
            ])
            ->add('category', EntityType::class, [
                'label'       => 'form.category',
                'class'       => Category::class,
                'choice_label'=> 'name',
                'required'    => false,
            ])
            ->add('tags', EntityType::class, [
                'label'       => 'form.tags',
                'class'       => Tag::class,
                'choice_label'=> 'name',
                'multiple'    => true,
                'expanded'    => true,
                'required'    => false,
            ])
            ->add('minPrice', MoneyType::class, [
                'label'    => 'form.search.min_price',
                'currency' => 'EUR',
                'required' => false,
                'attr'     => ['placeholder' => '0,00'],
            ])
            ->add('maxPrice', MoneyType::class, [
                'label'    => 'form.search.max_price',
                'currency' => 'EUR',
                'required' => false,
                'attr'     => ['placeholder' => '999,99'],
            ])
            ->add('expiresAtAfter', DateType::class, [
                'label'    => 'form.search.expires_after',
                'required' => false,
                'widget'   => 'single_text',
            ])
            ->add('expiresAtBefore', DateType::class, [
                'label'    => 'form.search.expires_before',
                'required' => false,
                'widget'   => 'single_text',
            ])
            ->add('sort', ChoiceType::class, [
                'label'    => false,
                'required' => true,
                'choices'  => [
                    'form.sort.trending'          => 'trending',
                    'form.sort.date_desc'      => 'date_desc',
                    'form.sort.date_asc'      => 'date_asc',
                    'form.sort.price_asc'    => 'price_asc',
                    'form.sort.price_desc'  => 'price_desc',
                    'form.sort.name_asc'           => 'name_asc',
                    'form.sort.name_desc'           => 'name_desc',
                    'form.sort.expires_asc' => 'expires_asc',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
