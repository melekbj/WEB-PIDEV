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
