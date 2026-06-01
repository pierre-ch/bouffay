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
                'label' => 'form.first_name',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'form.last_name',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email',
                'constraints' => [new NotBlank(), new Length(['max' => 100])],
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'form.locale',
                'choices' => [
                    'form.locale_fr' => 'fr',
                    'form.locale_en' => 'en',
                    'form.locale_pt' => 'pt',
                    'form.locale_es' => 'es',
                    'form.locale_it' => 'it',
                    'form.locale_de' => 'de',
                    'form.locale_ht' => 'ht',
                    'form.locale_ar' => 'ar',
                    'form.locale_ja' => 'ja',
                    'form.locale_ko' => 'ko',
                ],
            ])
            ->add('theme', ChoiceType::class, [
                'label' => 'form.theme',
                'choices' => [
                    'form.theme_light' => 'light',
                    'form.theme_dark' => 'dark',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
