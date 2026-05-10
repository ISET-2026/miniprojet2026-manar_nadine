<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\CategorieRecette;
use App\Entity\Recette;
use App\Entity\TagRecette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la recette',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Tarte aux pommes maison'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Décrivez votre recette en quelques mots (min 30 caractères)'],
            ])
            ->add('instructions', TextareaType::class, [
                'label' => 'Instructions',
                'attr' => ['class' => 'form-control', 'rows' => 8, 'placeholder' => 'Décrivez les étapes de préparation...'],
            ])
            ->add('tempsPreparation', IntegerType::class, [
                'label' => 'Temps de préparation (min)',
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            ->add('tempsCuisson', IntegerType::class, [
                'label' => 'Temps de cuisson (min)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('difficulte', ChoiceType::class, [
                'label' => 'Difficulté',
                'choices' => [
                    '🟢 Facile' => 'facile',
                    '🟡 Moyen' => 'moyen',
                    '🔴 Difficile' => 'difficile',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('nbPersonnes', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'attr' => ['class' => 'form-control', 'min' => 1, 'max' => 50],
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieRecette::class,
                'choice_label' => fn (CategorieRecette $c) => ($c->getIcone() ? $c->getIcone() . ' ' : '') . $c->getNom(),
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('tags', EntityType::class, [
                'class' => TagRecette::class,
                'choice_label' => 'nom',
                'label' => 'Tags',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'required' => false,
            ])
            ->add('publiee', CheckboxType::class, [
                'label' => 'Publier la recette',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
->add('ingredients', CollectionType::class, [
    'entry_type'    => IngredientType::class,
    'allow_add'     => true,
    'allow_delete'  => true,
    'by_reference'  => false,
    'label'         => 'Ingrédients',
    'entry_options' => ['label' => false],
])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPEG, PNG, WebP — max 2 Mo)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image JPEG, PNG ou WebP.',
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'accept' => 'image/jpeg,image/png,image/webp'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
