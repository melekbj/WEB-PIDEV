<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Twilio\Rest\Client;
use App\Form\RegisterType;
use App\Service\SendSmsService;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Store;
use App\Form\StoreType;
use App\Repository\StoreRepository;
use Symfony\Component\Security\Core\Security;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        // Get the current user
        $user = $this->getUser();
        
        
        
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            
        ]);
    }

  

   
    public function Baseindex(): Response
    {
        // Get the current user
        $user = $this->getUser();
        
        
        
        
        return $this->render('baseAdmin.html.twig', [
            'controller_name' => 'AdminController',
            

        ]);
    }


    #[Route('/liste_des_utilisateurs', name: 'app_users')]
    public function ListeU(): Response
    {
        // Get the current user
        $user = $this->getUser();
        
        //recuperer le repository
        $repository = $this->getDoctrine()->getRepository(User::class);
        //utiliser findAll() pour recuperer toutes les classes
        $users = $repository->createQueryBuilder('u')
        ->where('u.roles LIKE :roles1 OR u.roles LIKE :roles2')
        ->andWhere('u.etat <> :etat')
        ->orderBy('u.nom', 'ASC') 
        ->setParameters([
            'roles1' => '%ROLE_CLIENT%',
            'roles2' => '%ROLE_PARTNER%',
            'etat' => 1
        ])
        ->getQuery()
        ->getResult();


        return $this->render('admin/ListeUsers.html.twig', [
            'users' => $users,
            
        ]);
    }

    #[Route('/liste_des_partenaires', name: 'app_partners')]
    public function ListeP(): Response
    {
        // Get the current user
        $user = $this->getUser();
        
        
        //recuperer le repository
        $repository = $this->getDoctrine()->getRepository(User::class);
        //utiliser findAll() pour recuperer toutes les classes
        $users = $repository->findBy(['etat' => [1, -2]]);

        return $this->render('admin/ListePartners.html.twig', [
            'users' => $users,
            
        ]);
    }


    #[Route('/profile', name: 'app_profile')]
    public function updateProfile(Request $request)
    {
        
        return $this->render('admin/profile.html.twig', [
            
        ]);
    }

   

    
    #[Route('/store', name: 'app_store_index', methods: ['GET'])]
    public function liststore(StoreRepository $storeRepository, Security $security): Response
    {
        $stores = $storeRepository->findAll();
    
        return $this->render('admin/store/index.html.twig', [
            'stores' => $stores,
        ]);
    }
    
    

    // #[Route('/new/store', name: 'app_store_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, StoreRepository $storeRepository): Response
    // {
    //     $store = new Store();
    //     $form = $this->createForm(StoreType::class, $store);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $storeRepository->save($store, true);

    //         return $this->redirectToRoute('app_store_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('admin/store/new.html.twig', [
    //         'store' => $store,
    //         'form' =>  $form,
    //     ]);
    // }


    #[Route('/store/{id}', name: 'app_store_show', methods: ['GET'])]
    public function show(Store $store, Security $security, StoreRepository $storeRepository): Response
    {

        return $this->render('admin/store/show.html.twig', [
            'store' => $store,
        ]);
    }
    
    // #[Route('/{id}/edit', name: 'app_store_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, Store $store, StoreRepository $storeRepository): Response
    // {
    //     $form = $this->createForm(StoreType::class, $store);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $storeRepository->save($store, true);

    //         return $this->redirectToRoute('app_store_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('admin/store/edit.html.twig', [
    //         'store' => $store,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/store/delete/{id}', name: 'app_store_delete', methods: ['POST'])]
    public function delete(Request $request, Store $store): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($store);
            $entityManager->flush();
            
            $this->addFlash('success', 'Store deleted successfully');

        return $this->redirectToRoute('app_store_index');
    }
}


    


    

    
    




