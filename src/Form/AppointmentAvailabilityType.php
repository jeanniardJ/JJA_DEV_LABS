<?php

namespace App\Form;

use App\Entity\AppointmentAvailability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentAvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayOfWeek', ChoiceType::class, [
                'choices' => [
                    'Lundi' => 1,
                    'Mardi' => 2,
                    'Mercredi' => 3,
                    'Jeudi' => 4,
                    'Vendredi' => 5,
                    'Samedi' => 6,
                    'Dimanche' => 0,
                ],
                'label' => 'Jour de la semaine',
                'attr' => ['class' => 'bg-black border-lab-border text-white text-xs font-mono px-3 py-2 outline-none w-full mb-4 focus:border-lab-primary']
            ])
            ->add('startTime', TimeType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'label' => 'Heure de début',
                'attr' => ['class' => 'bg-black border-lab-border text-white text-xs font-mono px-3 py-2 outline-none w-full mb-4 focus:border-lab-primary']
            ])
            ->add('endTime', TimeType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'label' => 'Heure de fin',
                'attr' => ['class' => 'bg-black border-lab-border text-white text-xs font-mono px-3 py-2 outline-none w-full mb-4 focus:border-lab-primary']
            ])
            ->add('slotDuration', IntegerType::class, [
                'label' => 'Durée des créneaux (minutes)',
                'attr' => ['class' => 'bg-black border-lab-border text-white text-xs font-mono px-3 py-2 outline-none w-full mb-4 focus:border-lab-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppointmentAvailability::class,
        ]);
    }
}
