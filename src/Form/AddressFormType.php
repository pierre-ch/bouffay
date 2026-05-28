<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'form.street',
                'constraints' => [
                    new NotBlank(['message' => 'form.error.street_blank']),
                    new Length(['max' => 255]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'form.city',
                'constraints' => [
                    new NotBlank(['message' => 'form.error.city_blank']),
                    new Length(['max' => 255]),
                ],
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'form.zip_code',
                'constraints' => [
                    new NotBlank(['message' => 'form.error.zip_blank']),
                    new Length(['max' => 10]),
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'form.country',
                'constraints' => [
                    new NotBlank(['message' => 'form.error.country_blank']),
                    new Length(['max' => 255]),
                ],
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'form.is_default',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
