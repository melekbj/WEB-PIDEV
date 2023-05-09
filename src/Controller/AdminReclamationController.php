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
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;

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
    public function adminReclamations(t $requestReques): Response
    {
        $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findAll();
    
        usort($reclamations, function($a, $b) {
            $aStatus = $a->getEtat();
            $bStatus = $b->getEtat();
    
            if ($aStatus == $bStatus) {
                return 0;
            }
    
            if ($aStatus == 'pending') {
                return -1;
            }
    
            if ($bStatus == 'pending') {
                return 1;
            }
    
            if ($aStatus == 'accepted') {
                return -1;
            }
    
            if ($bStatus == 'accepted') {
                return 1;
            }
    
            if ($aStatus == 'refused') {
                return -1;
            }
    
            if ($bStatus == 'refused') {
                return 1;
            }
        });
    
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
#[Route('/admin/reclamationstat', name: 'reclamationstat')]

    public function etatstat()
    {
        // Get the reclamations with their status
        $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findAll();

        $accepted = 0;
        $refused = 0;
        $pending = 0;
    
        foreach ($reclamations as $reclamation) {
            switch ($reclamation->getEtat()) {
                case 'accepted':
                    $accepted++;
                    break;
                case 'refused':
                    $refused++;
                    break;
                case 'pending':
                    $pending++;
                    break;
            }
        }
    
        $pieChart = new PieChart();
        $data = [
            ['Etat', 'Nombre'],
            ['Accepted', $accepted],
            ['Refused', $refused],
            ['Pending', $pending],
        ];
        $pieChart->getData()->setArrayToDataTable($data);
        $pieChart->getOptions()->setTitle('Reclamations');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);
        return $this->render('reclamation/statreclamation.html.twig', [
            'chart' => $pieChart
        ]);
    }
#[Route('/reclamation/user/{client_id}', name: 'userReclamation')]
    public function userReclamation(int $client_id): Response
    {
        $reclamations = $this->getDoctrine()
            ->getRepository(Reclamation::class)
            ->findBy(['client_id' => $client_id]);

        return $this->render('reclamation/userreclamation.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
}


