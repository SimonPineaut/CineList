<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Saisissez un mot de passe',
                        ]),
                        new Length([
                            'max' => 4096,
                        ]),
                        new PasswordRequirements([
                            'minLength' => 8,
                            'requireLetters' => true,
                            'requireCaseDiff' => true,
                            'requireNumbers' => true,
                            'requireSpecialCharacter' => true,
                            'tooShortMessage' => 'Votre mot de passe doit faire au moins 8 caractères',
                            'missingLettersMessage' => 'Votre mot de passe doit inclure au moins 1 lettre',
                            'requireCaseDiffMessage' => 'Votre mot de passe doit contenir des majuscules et des minuscules',
                            'missingNumbersMessage' => 'Votre mot de passe doit inclure au moins 1 chiffre',
                            'missingSpecialCharacterMessage' => 'Votre mot de passe doit contenir au moins un caractère spécial',  
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                ],
                'invalid_message' => 'Les mots de passe ne sont pas identiques',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
