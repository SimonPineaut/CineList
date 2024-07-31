<?php

namespace App\Form;

use DateTime;
use App\Controller\GenreController;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AdvancedSearchType extends AbstractType
{
    public function __construct(private GenreController $genreController)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $genres = $this->genreController->getGenreFormOptions();

        $builder
            ->add('primary_release_year', IntegerType::class, [
                'required' => false,
                'label' => 'Année de sortie',
                'constraints' => [
                    new Range([
                        'min' => '1800',
                        'max' => '2050',
                        'notInRangeMessage' => 'L\'année doit être comprise entre{{ min }} et {{ max }}',
                    ]),
                ],
            ])
            ->add('primary_release_date_gte', IntegerType::class, [
                'required' => false,
                'label' => 'à partir de l\'année',
                'constraints' => [
                    new Range([
                        'min' => '1800',
                        'max' => '2050',
                        'notInRangeMessage' => 'L\'année doit être comprise entre{{ min }} et {{ max }}',
                    ]),
                ],
            ])
            ->add('primary_release_date_lte', IntegerType::class, [
                'required' => false,
                'label' => 'jusqu\'à l\'année',
                'constraints' => [
                    new Range([
                        'min' => '1800',
                        'max' => '2050',
                        'notInRangeMessage' => 'L\'année doit être comprise entre{{ min }} et {{ max }}',
                    ]),
                ],
            ])
            ->add('sort_by', ChoiceType::class, [
                'choices' => [
                    'Note ⭣' => 'vote_average.desc',
                    'Note ⭡' => 'vote_average.asc',
                    'Date de sortie ⭣' => 'primary_release_date.desc',
                    'Date de sortie ⭡' => 'primary_release_date.asc',
                    'Nombre de votes ⭣' => 'vote_count.desc',
                    'Nombre de votes ⭡' => 'vote_count.asc',
                    'Titre original ⭣' => 'original_title.desc',
                    'Titre original ⭡' => 'original_title.asc',
                    'Revenus ⭣' => 'revenue.desc',
                    'Revenus ⭡' => 'revenue.asc',
                ],
                'required' => false,
                'label' => 'Trier par',
                'attr' => [
                    'class' => 'advanced-search-select'
                ],
            ])
            ->add('vote_average_gte', IntegerType::class, [
                'required' => false,
                'label' => 'Note (min)',
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 9,
                        'notInRangeMessage' => 'La note doit être comprise entre {{ min }} et {{ max }}',
                    ]),
                ],
            ])
            ->add('vote_average_lte', IntegerType::class, [
                'required' => false,
                'label' => 'Note (max)',
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 10,
                        'notInRangeMessage' => 'La note doit être comprise entre {{ min }} et {{ max }}',
                    ]),
                ],
            ])
            ->add('vote_count_gte', IntegerType::class, [
                'required' => false,
                'label' => 'Nombre de votes (min)',
                // 'empty_data' => 100,
                // 'help' => 'Fixé à 100 si non défini',
                // 'help_attr'=> ['class' => 'text-secondary'],
                'constraints' => [
                    new Positive(),
                ],
            ])
            ->add('vote_count_lte', IntegerType::class, [
                'required' => false,
                'label' => 'Nombre de votes (max)',
                'constraints' => [
                    new Positive(),
                ],
            ])
            ->add('with_cast', TextType::class, [
                'required' => false,
                'label' => 'Avec acteurs',
            ])
            ->add('with_genres', ChoiceType::class, [
                'required' => false,
                'choices' => $genres,
                'label' => 'Avec les genres',
                'multiple' => true,
                'attr' => [
                    'class' => 'advanced-search-select'
                ],
            ])
            ->add('without_genres', ChoiceType::class, [
                'required' => false,
                'choices' => $genres,
                'label' => 'Sans les genres',
                'multiple' => true,
                'attr' => [
                    'class' => 'advanced-search-select'
                ],
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
