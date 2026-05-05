<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank(), new Length(['max' => 100])],
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'Langue',
                'choices' => [
                    'Français' => 'fr',
                    'English'  => 'en',
                ],
            ])
            ->add('theme', ChoiceType::class, [
                'label' => 'Thème',
                'choices' => [
                    'Clair' => 'light',
                    'Sombre' => 'dark',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
