<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Evenement;
 use App\Entity\EventType;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;





class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', DateTimeType::class, [
            'label' => 'Date',
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy    HH:mm:ss',
            'html5' => false,
            'data' => new \DateTime('now'),
        ])
       
        ->add('nbPlaces', IntegerType::class, [
            'constraints' => [
                new GreaterThan([
                    'value' => 0,
                    'message' => 'Please enter a number greater than zero for the "nbMax" field.',
                ]),
                new LessThanOrEqual([
                    'value' => 10,
                    'message' => 'The maximum number of places allowed is 10.',
                ]),
            ],
        ])
      
            ->add('user',EntityType::class, [
                'class' => user::class,
                'choice_label' => 'nom',
                'label' => 'User',
                'placeholder' => 'Choose a user',
                'required' => true,
             ])
            //  ->add('event', EntityType::class, [
            //     'class' => Evenement::class,
            //     'choice_label' => 'id',
            //     'label' => 'Event',
            //     'placeholder' => 'Choose an event',
            //     'required' => true,
            // ])
            ->add('save', SubmitType::class, [
                'label' => 'Reserver',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            
        ;
    }

    
    

    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
