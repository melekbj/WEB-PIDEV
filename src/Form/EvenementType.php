<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\EventType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;


class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date_debut', DateType::class, [
            'constraints' => [
                new NotBlank(),
                new Type(\DateTime::class),
                new LessThanOrEqual([
                    'propertyPath' => 'parent.all[date_fin].data',
                    'message' => 'The start date must be before the end date.',
                ]),
                new Callback(function ($data, ExecutionContextInterface $context) {
                    if ($data && $data->format('Y') !== '2023') {
                        $context->buildViolation('The start date must start with the year 2023.')
                            ->atPath('date_debut')
                            ->addViolation();
                    }
                }),
            ],
        ])
        ->add('date_fin', DateType::class, [
            'constraints' => [
                new NotBlank(),
                new Type(\DateTime::class),
                new Callback(function ($data, ExecutionContextInterface $context) {
                    if ($data && $data->format('Y') !== '2023') {
                        $context->buildViolation('The end date must start with the year 2023.')
                            ->atPath('date_fin')
                            ->addViolation();
                    }
                }),
            ],
        ])
            // ->add('imageEv',FileType::class, [
            //     'data_class' => null,
            //     'required' => true,
            //     'label' => 'Profile Picture',])
            ->add('imageFile', VichFileType::class, [
                'required' => true,
                'allow_delete' => true,
                'download_uri' => true,
            ])
                
            ->add('lieuEv' )
            ->add('titreEv')
                    
                
            
            ->add('DescEv')
            ->add('nbMax', IntegerType::class, [
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'Please enter a number greater than zero for the "nbMax" field.',
                    ]),
                ],
            ])
    
           ->add('type',EntityType::class
               , [
                 'class' => EventType::class,
                 'choice_label' => 'libelle',
                'label' => 'Evenement Type',
                    'placeholder' => 'Choose a type',
                    'required' => true,
                
                ])
        ;
    
    $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
        $evenement = $event->getData();
        $imageFile = $evenement->getImageEv();

        if ($imageFile instanceof UploadedFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where images are stored
            try {
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle the exception
            }

            $user->setImage($newFilename);
        }
    });
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }




}
