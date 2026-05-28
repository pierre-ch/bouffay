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
                'label'    => 'Nom',
                'required' => false,
                'attr'     => ['placeholder' => 'Rechercher…'],
            ])
            ->add('category', EntityType::class, [
                'label'       => 'Catégorie',
                'class'       => Category::class,
                'choice_label'=> 'name',
                'required'    => false,
            ])
            ->add('tags', EntityType::class, [
                'label'       => 'Tags',
                'class'       => Tag::class,
                'choice_label'=> 'name',
                'multiple'    => true,
                'expanded'    => true,
                'required'    => false,
            ])
            ->add('minPrice', MoneyType::class, [
                'label'    => 'Prix min',
                'currency' => 'EUR',
                'required' => false,
                'attr'     => ['placeholder' => '0,00'],
            ])
            ->add('maxPrice', MoneyType::class, [
                'label'    => 'Prix max',
                'currency' => 'EUR',
                'required' => false,
                'attr'     => ['placeholder' => '999,99'],
            ])
            ->add('expiresAtAfter', DateType::class, [
                'label'    => 'Expire après le',
                'required' => false,
                'widget'   => 'single_text',
            ])
            ->add('expiresAtBefore', DateType::class, [
                'label'    => 'Expire avant le',
                'required' => false,
                'widget'   => 'single_text',
            ])
            ->add('sort', ChoiceType::class, [
                'label'    => false,
                'required' => true,
                'choices'  => [
                    'Trier par : Tendance'          => 'trending',
                    'Trier par : Plus récents'      => 'date_desc',
                    'Trier par : Plus anciens'      => 'date_asc',
                    'Trier par : Prix croissant'    => 'price_asc',
                    'Trier par : Prix décroissant'  => 'price_desc',
                    'Trier par : Nom A→Z'           => 'name_asc',
                    'Trier par : Nom Z→A'           => 'name_desc',
                    'Trier par : Expiration proche' => 'expires_asc',
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
