<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EditReservation;
use App\Entity\Evenement;
 use App\Entity\EventType;

 
 use MercurySeries\FlashyBundle\FlashyNotifier;

 use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

 #[ORM\Entity(repositoryClass: ReservationRepository::class)]
 #[UniqueEntity(fields: ['nbPlaces'], message:'ce champ doit etre positive et ne depassent pas 10')]
#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    //#[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    //public function new(Request $request, ReservationRepository $reservationRepository): Response
    //{
     //   $reservation = new Reservation();
      //  $form = $this->createForm(ReservationType::class, $reservation);
      //  $form->handleRequest($request);

     //   if ($form->isSubmitted() && $form->isValid()) {
          //  $reservationRepository->save($reservation, true);

          //  return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
       // }

    //    return $this->renderForm('reservation/new.html.twig', [
      //      'reservation' => $reservation,
         //   'form' => $form,
     //   ]);
 //   }

    #[Route('/show/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route  ('/edit/{id}', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(EditReservation::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservationRepository->save($reservation, true);

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

 //   #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
  //  public function delete(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
//    {
    //    if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
     //       $reservationRepository->remove($reservation, true);
      //  }

     //   return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
  //  }
  #[Route('/delete/{id}', name: 'app_reservation_delete', methods: ['POST'])]
   public function delete(Request $request, Reservation $reservation ,FlashyNotifier $flashy,ReservationRepository $reservationRepository): Response
    {
        $reservationDate = $reservation->getDate();
        $now = new \DateTime();
 // Calculer la différence en heures entre la date de réservation et l'heure actuelle
        $diffHours = $reservationDate->diff($now)->h;
       

        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            // Vérifier si la différence en heures est inférieure ou égale à 24 heures
            if ($diffHours >= 24) {
              $em=$this->getDoctrine()->getManager();
                $em->remove($reservation); 
               $em->flush();
               $flashy->success('reservation successfully deleted ', 5000);
            }
          
        }

       
               return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);


    }  


   



}
