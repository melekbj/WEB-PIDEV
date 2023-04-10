<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Evenement;

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

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Positive;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', null, [
            'constraints' => [
                new Regex([
                    'pattern' => '/^[^0-9]*$/',
                    'message' => 'Le champ "Nom" ne doit pas contenir de chiffres.',
                ]),
            ],
        ])
            ->add('photo',FileType::class, [
                'data_class' => null,
                'required' => true,
                'label' => 'Profile Picture',])
                ->add('prix', null, [
                    'constraints' => [
                        new Positive([
                            'message' => 'Le prix doit être un nombre positif.',
                        ]),
                        new GreaterThan([
                            'value' => 0,
                            'message' => 'Le prix doit être supérieur à zéro.',
                        ]),
                    ],
                ])
                ->add('quantite', null, [
                    'constraints' => [
                        new Positive([
                            'message' => 'La quantité doit être un nombre positif.',
                        ]),
                        new GreaterThan([
                            'value' => 0,
                            'message' => 'La quantité doit être supérieure à zéro.',
                        ]),
                    ],
                ])
            ->add('etat')
            ->add('categorie',EntityType::class,
            ['class'=>Produit::class,
            'choice_label'=>'nom']);
            /*->add('type',EntityType::class
               , [
                 'class' => EventType::class,
                 'choice_label' => 'libelle',
                'label' => 'Evenement Type',
                    'placeholder' => 'Choose a type',
                    'required' => true,
                
                ])*/

            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $produit = $event->getData();
                $imageFile = $produit->getPhoto();
            
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
            
                    $produit->setPhoto($newFilename); // update the image filename
                }
            });
           
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
   
    
}
    
    
    

