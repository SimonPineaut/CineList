<?php

namespace App\Form;

use DateTime;
use Symfony\Component\Form\AbstractType;
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
                // 'label_attr' => ['class' => 'register-label'],
                'attr' => [
                    'class' => '',
                ],
                'constraints' => [
                    new Range([
                        'min' => '1900',
                        'max' => $currentYear,
                    ]),
                ],
            ])
            ->add('primary_release_date_gte', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de sortie (à partir de)',
            ])
            ->add('primary_release_date_lte', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de sortie (jusqu\'à)',
            ])
            // ->add('sort_by', ChoiceType::class, [
            //     'choices' => [
            //         'Popularité décroissante' => 'popularity.desc',
            //         'Popularité croissante' => 'popularity.asc',
            //         'Date de sortie décroissante' => 'release_date.desc',
            //         'Date de sortie croissante' => 'release_date.asc',
            //         'Revenus décroissants' => 'revenue.desc',
            //         'Revenus croissants' => 'revenue.asc',
            //         'Date de sortie principale décroissante' => 'primary_release_date.desc',
            //         'Date de sortie principale croissante' => 'primary_release_date.asc',
            //         'Titre original décroissant' => 'original_title.desc',
            //         'Titre original croissant' => 'original_title.asc',
            //         'Moyenne des votes décroissante' => 'vote_average.desc',
            //         'Moyenne des votes croissante' => 'vote_average.asc',
            //         'Nombre de votes décroissant' => 'vote_count.desc',
            //         'Nombre de votes croissant' => 'vote_count.asc',
            //     ],
            //     'required' => false,
            //     'label' => 'Trier par',
            //     'placeholder' => 'Sélectionnez une option',
            // ])
            // ->add('vote_average_gte', NumberType::class, [
            //     'required' => false,
            //     'label' => 'Note (min)',
            // ])
            // ->add('vote_average_lte', NumberType::class, [
            //     'required' => false,
            //     'label' => 'Note (max)',
            // ])
            // ->add('vote_count_gte', NumberType::class, [
            //     'required' => false,
            //     'label' => 'Nombre de votes (min)',
            // ])
            // ->add('vote_count_lte', NumberType::class, [
            //     'required' => false,
            //     'label' => 'Nombre de votes (max)',
            // ])
            // ->add('with_cast', TextType::class, [
            //     'required' => false,
            //     'label' => 'Avec acteurs',
            // ])
            // ->add('with_genres', TextType::class, [
            //     'required' => false,
            //     'label' => 'Avec genres',
            // ])
            // ->add('with_keywords', TextType::class, [
            //     'required' => false,
            //     'label' => 'Avec mots-clés',
            // ])
            // ->add('with_people', TextType::class, [
            //     'required' => false,
            //     'label' => 'Avec personnes',
            // ])
            // ->add('without_genres', TextType::class, [
            //     'required' => false,
            //     'label' => 'Sans genres',
            // ])
            // ->add('without_keywords', TextType::class, [
            //     'required' => false,
            //     'label' => 'Sans mots-clés',
            // ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'save'],
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
