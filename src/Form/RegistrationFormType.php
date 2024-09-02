<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Nom d\'utilisateur',
                'label_attr' => ['class' => 'register-label'],
                'row_attr' => [
                    'class' => 'row-div',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nom d\'utilisateur',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre nom d\'utilisateur doit faire au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'row_attr' => [
                    'class' => 'row-div',
                ],
                'label' => 'Email',
                'label_attr' => ['class' => 'register-label'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un email',
                    ]),
                    new Email([
                        'message' => 'Votre email est invalide : {{ value }}',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'Mot de passe',
                'label_attr' => ['class' => 'register-label'],
                'attr' => [
                    'class' => 'password-field form-control',
                    'autocomplete' => 'new-password',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un mot de passe',
                    ]),
                    new Length([
                        // max length allowed by Symfony for security reasons
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
