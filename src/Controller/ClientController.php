<?php

namespace App\Controller;

use App\Form\StoreType;
use App\Form\RatingType;
use App\Repository\StoreRepository;
use App\Repository\RatingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Store;
use App\Entity\Rating;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }
    #[Route('/client/show/{id?}', name: 'app_store_show_client')]
    public function show(?int $id, Request $request, StoreRepository $storeRepository, RatingRepository $ratingRepository, UserInterface $user): Response
    {
        if ($id === null) {
            return $this->redirectToRoute('app_client');
        } else {
            // Create a new Rating entity
            $userRating = new Rating();
            $form = $this->createForm(RatingType::class, $userRating);
            $form->handleRequest($request);   
            if ($form->isSubmitted() && $form->isValid()) {
                // Set the user and store for the rating entity
                $store = $storeRepository->find($id);
                $rating = $this->getDoctrine()
                ->getRepository(Rating::class)
                ->findRatingByStoreAndUser($id, $user->getId());
            
            if ($rating) {
                $rating->setRate($userRating->getRate());
               

                $ratingRepository->save($rating, true);
            } else {
                $userRating->setStore($store);
                $userRating->setUser($user);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($userRating);
                $entityManager->flush();            }

            }
            $store = $storeRepository->find($id);
            $rating = $ratingRepository->getAverageStoreRating($id);
            return $this->render('partner/store/show.html.twig', [
                'store' => $store,
                'rating' => $rating,
                'form' => $form->createView(), // pass the form view to the template
            ]);
        }
    }

    
}
