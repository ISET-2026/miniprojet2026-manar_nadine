<?php

namespace App\Form;

use App\Entity\TagRecette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagRecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du tag',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Végétarien'],
            ])
            ->add('couleur', ColorType::class, [
                'label' => 'Couleur (code hex)',
                'attr' => ['class' => 'form-control form-control-color'],
                'data' => '#3498db',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => TagRecette::class]);
    }
}
