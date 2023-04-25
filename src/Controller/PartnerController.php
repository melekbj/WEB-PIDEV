<?php

namespace App\Controller;

use App\Form\StoreType;
use App\Repository\StoreRepository;
use App\Repository\RatingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Store;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use MercurySeries\FlashyBundle\FlashyNotifier;


class PartnerController extends AbstractController
{
    #[Route('/partner', name: 'app_partner')]
    public function index(StoreRepository $storeRepository, Security $security): Response
    {
        $user = $security->getUser();
        $store = $storeRepository->findStoreByUserId($user->getId());
    
        if ($store !== null) {
            return $this->redirectToRoute('app_store_show_partner', [
                'id' => $store->getId(),
                'user' => $user
            ]);
        } else {
            // Handle the case where the user doesn't have a store
            // For example, you could redirect them to the new store page
            return $this->redirectToRoute('app_store_new_partner');
        }
    }
     
    
    #[Route('/partner/new/store', name: 'app_store_new_partner', methods: ['GET', 'POST'])]
    public function new(Request $request,Security $security,FlashyNotifier $flashy): Response
    {
        $store = new Store();
        $user = $security->getUser();
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($store);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_store_show_partner', ['id' => $store->getId()]);
            $flashy->success('Welcome to your newly createdstore', 'https://your-awesome-link.com');

        }
    
        return $this->render('partner/store/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
     
    #[Route('/partner/show/{id?}', name: 'app_store_show_partner')]
    public function show(?int $id, StoreRepository $storeRepository, RatingRepository $ratingRepository,FlashyNotifier $flashy): Response
    {//TODO: add the rating methode and test if it is first time create a new insert else do an edit   
        if ($id === null) {
            return $this->redirectToRoute('app_store_new_partner');
        } else {
            $store = $storeRepository->find($id);
            $rating = $ratingRepository->getAverageStoreRating($id);
            $flashy->success('Welcome to your store', 'https://your-awesome-link.com');

                     return $this->render('partner/store/show.html.twig', [
                'store' => $store,
                'rating' => $rating
            ]);
        }
    }
    
    #[Route('/partner/edit/{id}', name: 'app_store_edit_partner', methods: ['GET', 'POST'])]
    public function edit(Request $request, Store $store, StoreRepository $storeRepository,FlashyNotifier $flashy): Response
    {
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storeRepository->save($store, true);
            $flashy->success('your store has been updated!', 'https://your-awesome-link.com');

            return $this->redirectToRoute('app_partner', [], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('partner/store/edit.html.twig', [
            'store' => $store,
            'form' => $form       
         ]);
    }

    //#[Route('/partner/delete/{id}', name: 'app_store_delete_partner', methods: ['POST'])]
    //public function delete(Request $request, Store $store): Response
    //{
    //        $entityManager = $this->getDoctrine()->getManager();
    //        $entityManager->remove($store);
    //        $entityManager->flush();
    //        $this->addFlash('success', 'Store deleted successfully');
    //    return $this->redirectToRoute('app_partner');
    //} 


}
