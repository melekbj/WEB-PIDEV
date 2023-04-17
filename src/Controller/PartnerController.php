<?php

namespace App\Controller;

use App\Form\StoreType;
use App\Repository\StoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Store;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Security\Core\Security;

class PartnerController extends AbstractController
{
    #[Route('/partner', name: 'app_partner')]
    public function index(StoreRepository $storeRepository, Security $security): Response
    {
        $user = $security->getUser();
        $store = $storeRepository->findStoreByUserId($user->getId());
    
        if ($store !== null) {
            return $this->redirectToRoute('app_store_show_partner', ['id' => $store->getId()]);
        } else {
            // Handle the case where the user doesn't have a store
            // For example, you could redirect them to the new store page
            return $this->redirectToRoute('app_store_new_partner');
        }
    }
     
    
    #[Route('/partner/new/store', name: 'app_store_new_partner', methods: ['GET', 'POST'])]
    public function new(Request $request,Security $security): Response
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
        }
    
        return $this->render('partner/store/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
     
    #[Route('/partner/show/{id?}', name: 'app_store_show_partner')]
    public function show(?int $id, StoreRepository $storeRepository): Response
    {
        if ($id === null) {
            return $this->redirectToRoute('app_store_new_partner');
        } else {
            $store = $storeRepository->find($id);
            return $this->render('partner/store/show.html.twig', [
                'store' => $store,
            ]);
        }
    }
    
    #[Route('/partner/edit/{id}', name: 'app_store_edit_partner', methods: ['GET', 'POST'])]
    public function edit(Request $request, Store $store, StoreRepository $storeRepository): Response
    {
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storeRepository->save($store, true);

            return $this->redirectToRoute('app_partner', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('partner/store/edit.html.twig', [
            'store' => $store,
            'form' => $form       
         ]);
    }

    #[Route('/partner/delete/{id}', name: 'app_store_delete_partner', methods: ['POST'])]
    public function delete(Request $request, Store $store): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($store);
            $entityManager->flush();
            
            $this->addFlash('success', 'Store deleted successfully');

        return $this->redirectToRoute('app_partner');
    }
}
