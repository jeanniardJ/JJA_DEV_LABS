<?php

namespace App\Form;

use App\Entity\Lead;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// ... existing imports

class AppointmentBookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(message: 'Le nom est obligatoire.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(message: 'L\'email est obligatoire.'),
                    new Email(message: 'L\'email "{{ value }}" n\'est pas valide.'),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'required' => false,
            ])
            ->add('datetime', TextType::class, [
                 'constraints' => [
                    new NotBlank(message: 'Le créneau horaire est obligatoire.'),
                ],
                'mapped' => false, // Handled manually in controller for now
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // We'll handle data manually or via Lead entity if we wanted
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'appointment_booking',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
