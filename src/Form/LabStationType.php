<?php

namespace App\Form;

use App\Entity\LabStation;
use App\Enum\StationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LabStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la station',
                'attr' => ['placeholder' => 'Ex: Security Scanner'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Description de la station...', 'rows' => 3],
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône Lucide',
                'attr' => ['placeholder' => 'Ex: shield, cpu, binary, server...'],
                'help' => 'Nom d\'icône Lucide (lucide.dev/icons)',
            ])
            ->add('status', EnumType::class, [
                'label' => 'Statut',
                'class' => StationStatus::class,
                'choice_label' => fn(StationStatus $status) => $status->value,
            ])
            ->add('borderColor', ColorType::class, [
                'label' => 'Couleur de bordure',
            ])
            ->add('uptime', TextType::class, [
                'label' => 'Uptime',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 99.98%'],
            ])
            ->add('metricLabel', TextType::class, [
                'label' => 'Label métrique',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Scans/jour'],
            ])
            ->add('metricValue', TextType::class, [
                'label' => 'Valeur métrique',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 142'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position (ordre d\'affichage)',
                'attr' => ['min' => 0],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LabStation::class,
        ]);
    }
}
