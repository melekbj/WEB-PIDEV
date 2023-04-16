<?php

namespace App\Controller;

use App\Entity\CategorieStore;
use App\Form\CategorieStoreType;
use App\Repository\CategorieStoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Form\StoreType;
use App\Repository\StoreRepository;
use App\Entity\Store;

#[Route('/categorie/store')]
class CategorieStoreController extends AbstractController
{
    #[Route('/', name: 'app_categorie_store_index', methods: ['GET'])]
    public function index(CategorieStoreRepository $categorieStoreRepository): Response
    {
        return $this->render('admin/categorie_store/index.html.twig', [
            'categorie_stores' => $categorieStoreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_categorie_store_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategorieStoreRepository $categorieStoreRepository): Response
    {
        $categorieStore = new CategorieStore();
        $form = $this->createForm(CategorieStoreType::class, $categorieStore);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $categorieStoreRepository->save($categorieStore, true);
    
                return $this->redirectToRoute('app_categorie_store_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'This category already exists.');
                // Render the form again with the error message
                return $this->renderForm('admin/categorie_store/new.html.twig', [
                    'categorie_store' => $categorieStore,
                    'form' =>  $form,
                ]);
            }
        }

        return $this->renderForm('admin/categorie_store/new.html.twig', [
            'categorie_store' => $categorieStore,
            'form' =>  $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_store_show', methods: ['GET'])]
    public function show(CategorieStore $categorieStore): Response
    {
        return $this->render('admin/categorie_store/show.html.twig', [
            'categorie_store' => $categorieStore,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_store_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieStore $categorieStore, CategorieStoreRepository $categorieStoreRepository): Response
    {
        $form = $this->createForm(CategorieStoreType::class, $categorieStore);
        $form->handleRequest($request);

    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $categorieStoreRepository->save($categorieStore, true);
    
                return $this->redirectToRoute('app_categorie_store_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'This category already exists.');
                // Render the form again with the error message
                return $this->renderForm('admin/categorie_store/edit.html.twig', [
                    'categorie_store' => $categorieStore,
                    'form' =>  $form,
                ]);
            }
        }

        return $this->renderForm('admin/categorie_store/edit.html.twig', [
            'categorie_store' => $categorieStore,
            'form' =>  $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_store_delete', methods: ['POST'])]
    public function delete(Request $request, $id, CategorieStore $categorieStore, StoreRepository $StoreRepository, CategorieStoreRepository $categorieStoreRepository): Response
    {
        $storedefault = $categorieStoreRepository->find('37');
        $stores = $StoreRepository->findBy(['categorie' => $id]);
        foreach ($stores as $store) {
            $store->setCategorie($storedefault);
            $StoreRepository->save($store, true);
        }
        if ($this->isCsrfTokenValid('delete' . $categorieStore->getId(), $request->request->get('_token'))) {
            $categorieStoreRepository->remove($categorieStore, true);
        }
        return $this->redirectToRoute('app_categorie_store_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
