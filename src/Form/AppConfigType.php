<?php

namespace App\Form;

use App\Entity\AppConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('settingValue', TextareaType::class, [
                'label' => 'Valeur du réglage',
                'attr' => [
                    'class' => 'bg-black border-lab-border text-white text-xs font-mono px-3 py-2 outline-none w-full focus:border-lab-primary',
                    'rows' => 5
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppConfig::class,
        ]);
    }
}
