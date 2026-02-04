<?php

namespace App\Form;

use App\Entity\Lead;
use PixelOpen\CloudflareTurnstileBundle\Type\TurnstileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner votre nom.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner votre email.'),
                    new Email(message: 'L\'adresse email n\'est pas valide.'),
                ],
            ])
            ->add('subject', TextType::class, [
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un sujet.'),
                ],
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner votre message.'),
                    new Length(
                        min: 10,
                        minMessage: 'Votre message doit contenir au moins {{ limit }} caractères.',
                    ),
                ],
            ]);

        if ($options['enable_captcha']) {
            $builder->add('turnstile', TurnstileType::class, [
                'attr' => [
                    'data-action' => 'contact',
                    'data-theme' => 'dark',
                ],
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Veuillez valider le captcha.'),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lead::class,
            'enable_captcha' => true,
        ]);
    }
}