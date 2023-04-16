<?php

namespace App\Controller;

use App\Entity\Store;
use App\Form\StoreType;
use App\Repository\StoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/store')]
class StoreController extends AbstractController
{
    #[Route('/', name: 'app_store_index', methods: ['GET'])]
    public function index(StoreRepository $storeRepository, Security $security): Response
    {
        $stores = $storeRepository->findAll();
        $user = $security->getUser();
        $store = $storeRepository->findStoreByUserId($user->getId());
    
        return $this->render('admin/store/index.html.twig', [
            'stores' => $stores,
            'store' => $store,
        ]);
    }
    
    

    #[Route('/new', name: 'app_store_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StoreRepository $storeRepository): Response
    {
        $store = new Store();
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storeRepository->save($store, true);

            return $this->redirectToRoute('app_store_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/store/new.html.twig', [
            'store' => $store,
            'form' =>  $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_store_show', methods: ['GET'])]
    public function show(Store $store, Security $security, StoreRepository $storeRepository): Response
    {
        $user = $security->getUser();
        $store = $storeRepository->findStoreByUserId($user->getId());
        if ($store === null)
        {
            return $this->render('admin/store/new.html.twig');
        }
        return $this->render('admin/store/show.html.twig', [
            'store' => $store,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_store_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Store $store, StoreRepository $storeRepository): Response
    {
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storeRepository->save($store, true);

            return $this->redirectToRoute('app_store_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/store/edit.html.twig', [
            'store' => $store,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_store_delete', methods: ['POST'])]
    public function delete(Request $request, Store $store): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($store);
            $entityManager->flush();
            
            $this->addFlash('success', 'Store deleted successfully');

        return $this->redirectToRoute('app_store_index');
    }
}
