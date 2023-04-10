<?php

namespace App\Form;

           
use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\TypeReclamation;
class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('type', EntityType::class, [
                'class' => TypeReclamation::class,
                'choice_label' => 'nom', // the property to use as the option label
                'label' => 'Type',
                'expanded' => true,
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}

           
           
           
