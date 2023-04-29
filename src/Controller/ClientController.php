<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Evenement;
use App\Form\ReservationType;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ReservationRepository;
use Doctrine\Persistence\ManagerRegistry;





class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Reservation::class);
        //utiliser findAll() pour recuperer toutes les classes
        $users = $repository->findAll() ;
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
            'users' => $users
        ]);
    }


    #[Route('/updateU/{id}', name: 'app_edit')]
    public function updateU($id, Request $request, ReservationRepository $rep, ManagerRegistry $doctrine): Response
    {     
        // récupérer la réservation à modifier
        $reservation = $rep->find($id);
        
        // Vérifier si la date de la réservation est vieille de plus de 24 heures
        $dateReservation = $reservation->getDate();
        $now = new \DateTime();
        $interval = $now->diff($dateReservation);
        $hoursDiff = $interval->h + ($interval->days * 24);
    
        if ($hoursDiff <= 24) {
            // créer un formulaire
            $form = $this->createForm(ReservationType::class, $reservation);
    
            // récupérer les données saisies
            $form->handleRequest($request);
    
            // vérifier si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                // récupérer les données saisies
                $reservationUpdated = $form->getData();
    
                // Calculer la différence entre le nombre de places réservées avant et après la modification
                $nbPlacesReservedBefore = $reservation->getNbPlaces();
                $nbPlacesReservedAfter = $reservationUpdated->getNbPlaces();
                $nbPlacesDiff = $nbPlacesReservedBefore - $nbPlacesReservedAfter;
    
                // Incrémenter ou décrémenter le champ nbMax par la différence de places réservées
                $evenement = $reservation->getEvent();
                $evenement->setNbMax($evenement->getNbMax() + $nbPlacesDiff);
    
                // Persister les données
                $em = $doctrine->getManager();
                $em->persist($reservationUpdated);
                $em->flush();
    
                // Flash message
                $this->addFlash('success', 'Reservation updated successfully!');
                return $this->redirectToRoute('app_client');
            }
    
            return $this->render('client/editReservation.html.twig', [
                'form' => $form->createView(),
            ]);
        } else {
            // La réservation ne peut pas être modifiée car moins de 24 heures se sont écoulées depuis la réservation
            $this->addFlash('error', 'Reservation cannot be updated as less than 24 hours have passed since it was made.');
            return $this->redirectToRoute('app_client');
        }
    }

    #[Route('/delete/{id}', name: 'app_deleteReservation')]
    public function deleteEvent($id, ReservationRepository $rep, ManagerRegistry $doctrine ): Response
    {
        //recuperer la réservation à supprimer
        $reservation = $rep->find($id);
    
        // Vérifier si la date de la réservation est vieille de plus de 24 heures
        $dateReservation = $reservation->getDate();
        $now = new \DateTime();
        $interval = $now->diff($dateReservation);
        $hoursDiff = $interval->h + ($interval->days * 24);
    
        if ($hoursDiff <= 24) {
            $rep=$doctrine->getManager();
            $nbPlacesReserved = $reservation->getNbPlaces();
    
            // Décrémenter le champ nbMax par le nombre de places réservées
            $evenement = $reservation->getEvent();
            $evenement->setNbMax($evenement->getNbMax() + $nbPlacesReserved);
    
            // Supprimer la réservation        
            $rep->remove($reservation);
            $rep->flush();
    
            // Flash message
            $this->addFlash('success', 'Reservation deleted successfully!');
            return $this->redirectToRoute('app_client');
        } else {
            // La réservation ne peut pas être supprimée car moins de 24 heures se sont écoulées depuis la réservation
            $this->addFlash('error', 'Reservation cannot be deleted as less than 24 hours have passed since it was made.');
            return $this->redirectToRoute('app_client');
        }
    }
    

}


