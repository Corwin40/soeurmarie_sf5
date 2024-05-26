<?php

namespace App\Form;

use App\Entity\Admin\member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'En cochant la case, je consens au traitement des informations saisies afin de réaliser des achats sur le site "cartesdeprieres.com". Je peux me désinscrire à tout moment en suivant la procédure décrite sur le lien disponible dans les mentions légales.',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Je consens au traitement des informations saisies afin de réaliser des achats sur le site "cartesdeprieres.com". Je peux me désinscrire à tout moment en suivant la procédure décrite sur le lien disponible dans les mentions légales.',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'entrez un mot de passe',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Nouveau password',
                ],
                'second_options' => [
                    'label' => 'Répétez le Password',
                ],
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => true,
            ])
            ->add('firstName')
            ->add('lastName')
            ->add('adress1')
            ->add('Adress2')
            ->add('zipcode')
            ->add('city')
            ->add('phoneDesk')
            ->add('phoneGsm')
            ->add('email')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => member::class,
        ]);
    }
}
