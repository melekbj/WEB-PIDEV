<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reclamation;
use App\Entity\TypeReclamation;
use App\Form\ReclamationType;
use App\Form\ReclamationTypeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class AdminReclamationController extends AbstractController

{
    #[Route('/admind', name: 'admin')]
    public function index(): Response
    {
        return $this->render('reclamation/admin.html.twig', [
            'controller_name' => 'AdminReclamationController',
        ]);
    }
    
    #[Route('/admin/reclamation', name: 'adminReclamation')]
    public function adminReclamations(Request $request): Response
    {
        $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findAll();
    
        return $this->render('reclamation/traiterReclamation.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    public function handleReclamation(Request $request, EntityManagerInterface $entityManager, int $id, string $etat): Response
{
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
    
    if (!$reclamation) {
        throw $this->createNotFoundException(
            'No reclamation found for id '.$id
        );
    }

    $reclamation->setEtat($etat);
    $entityManager->flush();

    return $this->redirectToRoute('adminReclamation');
}

#[Route('/admin/reclamation/accept/{id}', name: 'adminReclamationAccept')]
public function acceptReclamation(Request $request, EntityManagerInterface $entityManager, int $id): Response
{
    $this->handleReclamation($request, $entityManager, $id, 'accepted');

    return $this->redirectToRoute('adminReclamation');
}


#[Route('/admin/reclamation/refuse/{id}', name: 'adminReclamationRefuse')]
public function refuseReclamation(Request $request, EntityManagerInterface $entityManager, int $id): Response
{
    $this->handleReclamation($request, $entityManager, $id, 'refused');

    return $this->redirectToRoute('adminReclamation');
}

#[Route('/admin/addreclamationtype', name: 'addreclamationtype')]
public function add(Request $request): Response
{
    $reclamationType = new TypeReclamation();
    $form = $this->createForm(ReclamationTypeType::class, $reclamationType);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($reclamationType);
        $entityManager->flush();

        $this->addFlash('success', 'Reclamation type added successfully.');

        return $this->redirectToRoute('addreclamationtype');
    }

    return $this->render('reclamation/addreclamationtype.html.twig', [
        'form' => $form->createView(),
    ]);
}


}
