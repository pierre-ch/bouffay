<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

class ReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices' => [
                    '⭐ 1 — Très mauvais' => 1,
                    '⭐⭐ 2 — Mauvais'    => 2,
                    '⭐⭐⭐ 3 — Correct'  => 3,
                    '⭐⭐⭐⭐ 4 — Bien'   => 4,
                    '⭐⭐⭐⭐⭐ 5 — Excellent' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [new NotBlank(), new Range(['min' => 1, 'max' => 5])],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => ['rows' => 4, 'placeholder' => 'Décrivez votre expérience avec ce vendeur…'],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'max' => 1000]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Review::class]);
    }
}
