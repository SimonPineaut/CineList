<?php

namespace App\Form;

use DateTime;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Positive;

class AdvancedSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $now = new DateTime();
        $currentYear = $now->format("Y");

        $builder
            ->add('primary_release_year', IntegerType::class, [
                'required' => false,
                'label' => 'Année de sortie',
                'constraints' => [
                    new Positive([
                        'message' => 'Année de sortie invalide'
                    ]),
                ],
            ])
            ->add('primary_release_date_gte', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de sortie (min)',
                'constraints' => [
                    new Range([
                        'min' => '1900',
                        'max' => $currentYear,
                    ]),
                ],
            ])
            ->add('primary_release_date_lte', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de sortie (max)',
            ])
            ->add('sort_by', ChoiceType::class, [
                'choices' => [
                    'Note ⭣' => 'vote_average.desc',
                    'Note ⭡' => 'vote_average.asc',
                    'Titre original ⭣' => 'original_title.desc',
                    'Titre original ⭡' => 'original_title.asc',
                    'Date de sortie ⭣' => 'primary_release_date.desc',
                    'Date de sortie ⭡' => 'primary_release_date.asc',
                    'Nombre de votes ⭣' => 'vote_count.desc',
                    'Nombre de votes ⭡' => 'vote_count.asc',
                    'Revenus ⭣' => 'revenue.desc',
                    'Revenus ⭡' => 'revenue.asc',
                ],
                'required' => false,
                'label' => 'Trier par',
                'placeholder' => 'Sélectionnez une option',
            ])
            ->add('vote_average_gte', NumberType::class, [
                'required' => false,
                'label' => 'Note (min)',
            ])
            ->add('vote_average_lte', NumberType::class, [
                'required' => false,
                'label' => 'Note (max)',
            ])
            ->add('vote_count_gte', NumberType::class, [
                'required' => false,
                'label' => 'Nombre de votes (min)',
            ])
            ->add('vote_count_lte', NumberType::class, [
                'required' => false,
                'label' => 'Nombre de votes (max)',
            ])
            ->add('with_cast', TextType::class, [
                'required' => false,
                'label' => 'Avec acteurs',
            ])
            ->add('with_genres', TextType::class, [
                'required' => false,
                'label' => 'Avec genres',
            ])
            ->add('without_genres', TextType::class, [
                'required' => false,
                'label' => 'Sans genres',
            ])
            ->add('with_keywords', TextType::class, [
                'required' => false,
                'label' => 'Avec mots-clés',
            ])
            ->add('without_keywords', TextType::class, [
                'required' => false,
                'label' => 'Sans mots-clés',
            ])
            ->add('with_people', TextType::class, [
                'required' => false,
                'label' => 'Avec personnes',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
