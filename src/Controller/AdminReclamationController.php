<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reclamation;
use App\Form\ReclamationType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class AdminReclamationController extends AbstractController

{
    #[Route('/adminReclamation', name: 'adminReclamation')]
    public function index(Request $request): Response
    {
        $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findAll();
    
        return $this->render('reclamation/admin.html.twig', [
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

#[Route('/adminReclamation/accept/{id}', name: 'adminReclamationAccept')]
public function acceptReclamation(Request $request, EntityManagerInterface $entityManager, int $id): Response
{
    $this->handleReclamation($request, $entityManager, $id, 'accepted');

    return $this->redirectToRoute('adminReclamation');
}


#[Route('/adminReclamation/refuse/{id}', name: 'adminReclamationRefuse')]
public function refuseReclamation(Request $request, EntityManagerInterface $entityManager, int $id): Response
{
    $this->handleReclamation($request, $entityManager, $id, 'refused');

    return $this->redirectToRoute('adminReclamation');
}


}
