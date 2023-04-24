<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Entity\TypeReclamation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ReclamationController extends AbstractController
{
    #[Route('/addReclamationProduit', name: 'app_reclamation')]
    public function addAction(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $reclamation = new Reclamation();
        $typeReclamation = $entityManager->getRepository(TypeReclamation::class)->findOneBy(['nom' => 'Produit']);
        $reclamation->setEtat('pending'); // Set default value for etat
        $reclamation->setType($typeReclamation); // Set default value for type
        $reclamation->setDate(new \DateTime());

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_reclamation');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            
            $reclamation->setDescription($form->get('description')->getData());
     
            $image = $form->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('reclamation_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                 $message = 'An error occurred while uploading the file: ' . $e->getMessage();
                $session->getFlashBag()->add('error', $message);

                // Redirect back to the form
                return $this->redirectToRoute('app_reclamation');                }

                $reclamation->setimage($newFilename);
            }
            $reclamation->setClientId(7);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Reclamation added successfully!');
            return $this->redirectToRoute('app_reclamation');
        }

        return $this->render('reclamation/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
 
}
