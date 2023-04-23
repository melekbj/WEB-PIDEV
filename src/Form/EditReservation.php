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




class EditReservation extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', DateTimeType::class)
       
        ->add('nbPlaces')
      
            ->add('user',EntityType::class, [
                'class' => user::class,
                'choice_label' => 'id',
                'label' => 'User',
                'placeholder' => 'Choose a type',
                'required' => true,
             ])
             ->add('event', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => 'id',
                'label' => 'Event',
                'placeholder' => 'Choose an event',
                'required' => true,
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
