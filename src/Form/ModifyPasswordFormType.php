<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;

class ModifyPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control password-field',
                ],
                'label' => 'Mot de passe actuel',
                'label_attr' => ['class' => 'register-label'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe actuel',
                    ]),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control password-field',
                ],
                'label' => 'Nouveau mot de passe',
                'label_attr' => ['class' => 'register-label'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nouveau mot de passe',
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
            ])
            ->add('confirmPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control password-field',
                ],
                'label' => 'Confirmer le mot de passe',
                'label_attr' => ['class' => 'register-label'],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer votre nouveau mot de passe',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
