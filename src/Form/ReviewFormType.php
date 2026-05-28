<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('rating', HiddenType::class, [
                'constraints' => [new NotBlank(), new Range(['min' => 1, 'max' => 5])],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'form.review_content',
                'attr' => ['rows' => 4, 'placeholder' => 'form.review_placeholder'],
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
