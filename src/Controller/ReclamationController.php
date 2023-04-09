<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Entity\TypeReclamation;
use Doctrine\ORM\EntityManagerInterface;
class ReclamationController extends AbstractController
{
    #[Route('/addReclamationProduit', name: 'app_reclamation')]
    public function addAction(Request $request,EntityManagerInterface $entityManager)
    {
        $reclamation = new Reclamation();
        $typeReclamation = $entityManager->getRepository(TypeReclamation::class)->findOneBy(['nom' => 'Produit']);
        $reclamation->setEtat('pending'); // Set default value for etat
        $reclamation->setType($typeReclamation); // Set default value for type
        $reclamation->setDate(new \DateTime());
        
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setDescription($form->get('description')->getData());
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
