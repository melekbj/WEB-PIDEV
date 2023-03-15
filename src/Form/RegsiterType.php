<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                  'Client' => 'ROLE_CLIENT',
                  'Partner' => 'ROLE_PARTNER',
                  'Admin' => 'ROLE_ADMIN',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
            ])
            ->add('nom')
            ->add('prenom')
            ->add('age')
            ->add('adresse')
            ->add('image')
            ->add('genre',ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                  'Homme' => 'Homme',
                  'Femme' => 'Femme',
                  'Autre' => 'Autre',
                ],
            ])
            ->add('phone')
            // ->add('etat', IntegerType::class, [
            //     'required' => true,
            //     'empty_data' => 0,
            // ])
            
            ->add('save', SubmitType::class, [
                'label' => 'Register',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
         // Data transformer
        //  $builder->get('roles')
        //  ->addModelTransformer(new CallbackTransformer(
        //      function ($rolesArray) {
        //          // transform the array to a string
        //          return count($rolesArray)? $rolesArray[0]: null;
        //      },
        //      function ($rolesString) {
        //          // transform the string back to an array
        //          return [$rolesString];
        //     }
        //     ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
