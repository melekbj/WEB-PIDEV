<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Store;
use App\Entity\Rating;
use App\Form\UserType;
use App\Entity\Produit;
use Twilio\Rest\Client;
use App\Entity\Commande;
use App\Entity\Categorie;
use App\Entity\Evenement;
use App\Entity\EventType;
use App\Form\RegisterType;
use App\Entity\Reclamation;
use App\Entity\Reservation;
use App\Form\FormEventType;
use App\Entity\CategorieStore;
use App\Entity\DetailCommande;
use App\Entity\TypeReclamation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategorieStoreRepository;
use App\Repository\TypeReclamationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository(User::class);
        // Get the count of users with etat = 0
      
 

        

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
        ]);
    }


    #[Route('/liste_des_utilisateurs', name: 'app_users')]
    public function ListeU( UserRepository $userRepository, Request $request): Response
    {
        // Get the current user
        $user = $this->getUser();
        //find all users 
        $users = $userRepository->findAll();
        // Get the image associated with the user
        $image = $user->getImage();


        
       
        return $this->render('admin/ListeUsers.html.twig', [
            'users' => $users,
        ]);
    }


    #[Route('/liste_des_partenaires', name: 'app_partners')]
    public function ListeP(Request $request): Response
    {
        // Get the current user
        $user = $this->getUser();
        
        // Get the image associated with the user
        $image = $user->getImage();
        //recuperer le repository
        $repository = $this->getDoctrine()->getRepository(User::class);
        //utiliser findAll() pour recuperer toutes les classes
        $users = $repository->findByRoles('ROLE_PARTNER');
    
        return $this->render('admin/ListePartners.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/deleteU/{id}', name: 'app_deleteU')]
    public function deleteU($id, UserRepository $rep, ManagerRegistry $doctrine ): Response
    {

        //recuperer la classe a supprimer
        $users = $rep->find($id);
        $rep=$doctrine->getManager();
        //supprimer la classe        
        $rep->remove($users);
        $rep->flush();
        //flash message
        $this->addFlash('success', 'User deleted successfully!');
        return $this->redirectToRoute('app_users'); 
        
    }




    #[Route('/profile', name: 'app_profile')]
    public function updateProfile(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        // Get the image associated with the user
        $image = $user->getImage();

        // Create a new userType form and populate it with the user's data
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the updated user information to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect to the user's profile page with a success message
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_profile');
            
        }
        // If the form was not submitted or is not valid, render the profile edit form
        return $this->render('admin/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'image' => $image,
        ]);
    }

    
    #[Route('/updatePassword', name: 'update_password', methods:['POST'])] 
    public function updatePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // Get the current user
        $user = $this->getUser();
 
        // Get the submitted form data
        $actualPassword = $request->request->get('actualPassword');
        $newPassword = $request->request->get('newPassword');
        $confirmPassword = $request->request->get('ConfirmPassword');
        
        // Check that the current password is correct
        if (!$passwordEncoder->isPasswordValid($user, $actualPassword)) {
            $this->addFlash('error', 'Current password is incorrect.');
            return $this->redirectToRoute('app_profile');
        }
        
        // Check that the new password and confirm password match
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'New password and confirm password do not match.');
            return $this->redirectToRoute('app_profile');
        }
        
        // Encode the new password and update the user's password
        $newEncodedPassword = $passwordEncoder->encodePassword($user, $newPassword);
        $user->setPassword($newEncodedPassword);
        $this->getDoctrine()->getManager()->flush();
        
        // Redirect to a success page
        $this->addFlash('success', 'Password updated successfully.');
        return $this->redirectToRoute('app_profile');
    }


    #[Route('/blockU/{id}', name: 'app_blockU')]
    public function blockU($id, UserRepository $rep, ManagerRegistry $doctrine): Response
    {
        // Get the user to deactivate
        $user = $rep->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set the user's etat to -1
        $user->setEtat(-1);



        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();
        
      


        //flash message
        $this->addFlash('success', 'User blocked successfully!');

        return $this->redirectToRoute('app_users');
    }


    #[Route('/approveU/{id}', name: 'app_approveU')]
    public function approveU($id, UserRepository $rep, ManagerRegistry $doctrine): Response
    {
        // Get the user to deactivate
        $user = $rep->find($id);

   

        $em = $doctrine->getManager();
        $em->flush();
        
       


        //flash message
        $this->addFlash('success', 'User approved successfully!');

        return $this->redirectToRoute('app_users');
    }

   
    
// ................................................ Gesion Events..................................................................................................... 

    #[Route('/event/new', name: 'app_events_new')]
    public function newEvent(Request $request, PersistenceManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        // Get the image associated with the user
        $image = $user->getImage();

        $events = new Evenement();
        $events->setDateDebut(new \DateTime());
        $events->setDateFin(new \DateTime());
        $form = $this->createForm(FormEvent::class, $events);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($events);
            $entityManager->flush();
            $this->addFlash('success', 'Event ajouté avec succès');
            return $this->redirectToRoute('app_events_liste');
        }
        
        return $this->render('admin/Events/addEvent.html.twig', [
            'form' =>$form->createView(),
            'image' => $image,
        ]);
    }

    #[Route('/events/liste', name: 'app_events_liste')]
    public function newType(Request $request, PersistenceManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Get the image associated with the user
        $image = $user->getImage();

        $eventRepository = $entityManager->getRepository(Evenement::class);
        $events = $eventRepository->findAll();

        $event = new EventType();
        $form = $this->createForm(FormEventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Event type ajouté avec succès');
            return $this->redirectToRoute('app_events_liste');
        }
        
        return $this->render('admin/Events/listeEvents.html.twig', [
            'typeForm' =>$form->createView(),
            'image' => $image,
            'events' => $events,
        ]);
    }

    #[Route('/deleteEvent/{id}', name: 'app_deleteEvent')]
    public function deleteEvent($id, EvenementRepository $rep, ManagerRegistry $doctrine ): Response
    {
        //recuperer la classe a supprimer
        $events = $rep->find($id);
        $rep=$doctrine->getManager();
        //supprimer la classe        
        $rep->remove($events);
        $rep->flush();
        //flash message
        $this->addFlash('success', 'Event deleted successfully!');
        return $this->redirectToRoute('app_events_liste'); 
        
    }

    #[Route('/updateEvent/{id}', name: 'app_updateEvent')]
    public function updateEvent($id, Request $request, EvenementRepository $rep, ManagerRegistry $doctrine): Response
    {
        // Get the current user
        $user = $this->getUser();
        // Get the image associated with the user
        $image = $user->getImage();
        // récupérer la classe à modifier
        $events = $rep->find($id);
        // créer un formulaire
        $form = $this->createForm(FormEvent::class, $events);
        // récupérer les données saisies
        $form->handleRequest($request);
        // vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // récupérer les données saisies
            $events = $form->getData();
            // persister les données
            $rep = $doctrine->getManager();
            $rep->persist($events);
            $rep->flush();
            //flash message
            $this->addFlash('success', 'Event updated successfully!');
            return $this->redirectToRoute('app_events_liste');
        }
        return $this->render('admin/Events/EditEvents.html.twig', [
            'form' => $form->createView(),
            'image' => $image,
        ]);
    }

// ................................................ Gesion Types Events..................................................................................................... 

    #[Route('/type_events/liste', name: 'app_types_events_liste')]
    public function EventsTypes(Request $request, PersistenceManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Get the image associated with the user
        $image = $user->getImage();

        $eventRepository = $entityManager->getRepository(EventType::class);
        $events = $eventRepository->findAll();

        return $this->render('admin/Events/listeTypesEvents.html.twig', [
            // 'typeForm' =>$form->createView(),
            'image' => $image,
            'events' => $events,
        ]);
    }

    #[Route('/delete_Event_type/{id}', name: 'app_deleteEventType')]
    public function deleteEventType($id, EventTypeRepository $rep, ManagerRegistry $doctrine ): Response
    {
        //recuperer la classe a supprimer
        $events = $rep->find($id);
        $rep=$doctrine->getManager();
        //supprimer la classe        
        $rep->remove($events);
        $rep->flush();
        //flash message
        $this->addFlash('success', 'Event types deleted successfully!');
        return $this->redirectToRoute('app_types_events_liste'); 
        
    }

// .........................................Gestion Store..........................................................


    #[Route('/list_stores', name: 'app_store_index', methods: ['GET','POST'])]
    public function liststore(StoreRepository $storeRepository, EntityManagerInterface $entityManager,Request $request): Response
    {
            $user = $this->getUser();
            // Get the image associated with the user
            $image = $user->getImage();
            $location=$request->get('localtion');
            $nom=$request->get('nom');
    
    
            $stores = $storeRepository->findLocalisationOrNom($location,$nom);
            $averageRatings = [];
                
            foreach ($stores as $store) {
                $ratings = $entityManager->getRepository(Rating::class)->findBy(['store' => $store]);
                $ratingValue = 0;
                $count = count($ratings);
                if ($count > 0) {
                    foreach ($ratings as $rating) {
                        $ratingValue += $rating->getRate();
                    }
                    $averageRating = $ratingValue / $count;
                } else {
                    $averageRating = 0;
                }
                $averageRatings[$store->getId()] = $averageRating;
            }

            $storecategory = new CategorieStore();
            $form = $this->createForm(CategorieStoreType::class, $storecategory);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($storecategory);
                $entityManager->flush();

                $this->addFlash('success', 'Category store added successfully.');

                return $this->redirectToRoute('app_store_index');
            }
    
            return $this->render('admin/store/index.html.twig', [
                'stores' => $stores,
                'averageRatings' => $averageRatings,
                'image' => $image,
                'typeForm' => $form->createView(),
            ]);
    }

    #[Route('/store/{id}', name: 'app_store_show', methods: ['GET'])]
    public function show(Store $store, Security $security, StoreRepository $storeRepository): Response
    {
        $user = $this->getUser();
        // Get the image associated with the user
        $image = $user->getImage();
        return $this->render('admin/store/show.html.twig', [
            'store' => $store,
            'image' => $image,
        ]);
    }

    #[Route('/categorie_store', name: 'app_categorie_store_index', methods: ['GET'])]
    public function ListCategorie(CategorieStoreRepository $categorieStoreRepository,PersistenceManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {
            $user = $this->getUser();
            // Get the image associated with the user
            $image = $user->getImage();

            $cat = $entityManager->getRepository(CategorieStore::class);
            $categoriestores = $cat->findAll();
        
            return $this->render('admin/categorie_store/index.html.twig', [
                'categorie_stores' => $categoriestores,
                'image' => $image,
            ]);
    }

// .........................................Gestion Categorie Store..........................................................

    // #[Route('/categorie_store/new', name: 'app_categorie_store_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, CategorieStoreRepository $categorieStoreRepository): Response
    // {
    //     $categorieStore = new CategorieStore();
    //     $form = $this->createForm(CategorieStoreType::class, $categorieStore);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         try {
    //             $categorieStoreRepository->save($categorieStore, true);
    
    //             return $this->redirectToRoute('app_categorie_store_index', [], Response::HTTP_SEE_OTHER);
    //         } catch (UniqueConstraintViolationException $e) {
    //             $this->addFlash('error', 'This category already exists.');
    //             // Render the form again with the error message
    //             return $this->renderForm('admin/categorie_store/new.html.twig', [
    //                 'categorie_store' => $categorieStore,
    //                 'form' =>  $form,
    //             ]);
    //         }
    //     }

    //     return $this->renderForm('admin/categorie_store/new.html.twig', [
    //         'categorie_store' => $categorieStore,
    //         'form' =>  $form,
    //     ]);
    // }


    #[Route('/categorie_store/{id}/edit', name: 'app_categorie_store_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieStore $categorieStore, CategorieStoreRepository $categorieStoreRepository): Response
    {
        $user = $this->getUser();
            // Get the image associated with the user
            $image = $user->getImage();
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
            'image' => $image,
        ]);
    }

    #[Route('/categorie_store/{id}', name: 'app_categorie_store_delete', methods: ['POST'])]
    public function delete(Request $request, $id, CategorieStore $categorieStore, StoreRepository $StoreRepository, CategorieStoreRepository $categorieStoreRepository): Response
    {
        $storedefault = $categorieStoreRepository->find('6');
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

// .........................................Gestion Reclamation..........................................................

    #[Route('/liste_des_reclamation', name: 'app_reclamations_list')]
    public function ListeReclamations(Request $request,EntityManagerInterface $entityManager): Response
    {
            // Get the current user
            $user = $this->getUser();
            // Get the image associated with the user
            $image = $user->getImage();

            $recRepo = $entityManager->getRepository(Reclamation::class);
            $reclamations = $recRepo->findAll();

            $reclamationType = new TypeReclamation();
            $form = $this->createForm(ReclamationTypeType::class, $reclamationType);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($reclamationType);
                $entityManager->flush();

                $this->addFlash('success', 'Reclamation type added successfully.');

                return $this->redirectToRoute('app_reclamations_list');
            }
            

            return $this->render('admin/reclamation/listeReclamation.html.twig', [
                'image' => $image,
                'reclamations' => $reclamations,
                'typeForm' => $form->createView(),
            ]);
    }

    #[Route('/acceptR/{id}', name: 'app_acceptR')]
    public function acceptR($id, ReclamationRepository $rep, ManagerRegistry $doctrine): Response
    {
            // Get the reclamation to deactivate
            $reclamation = $rep->find($id);
    
            if (!$reclamation) {
                throw $this->createNotFoundException('Reclamation not found');
            }
    
            // Set the reclamation's etat to -1
            $reclamation->setEtat('accepted');
    
    
            $em = $doctrine->getManager();
            $em->persist($reclamation);
            $em->flush();
            
          
            //flash message
            $this->addFlash('success', 'reclamation accepeted successfully!');
    
            return $this->redirectToRoute('app_reclamations_list');
    }

    #[Route('/refuseR/{id}', name: 'app_refusR')]
    public function refusR($id, ReclamationRepository $rep, ManagerRegistry $doctrine): Response
    {
            // Get the reclamation to deactivate
            $reclamation = $rep->find($id);
    
            if (!$reclamation) {
                throw $this->createNotFoundException('Reclamation not found');
            }
    
            // Set the reclamation's etat to -1
            $reclamation->setEtat('refused');
    
    
            $em = $doctrine->getManager();
            $em->persist($reclamation);
            $em->flush();
            
          
            //flash message
            $this->addFlash('success', 'reclamation refused successfully!');
    
            return $this->redirectToRoute('app_reclamations_list');
    }

// .........................................Gestion Reclamation Type..........................................................
    
    #[Route('/type_reclamation/liste', name: 'app_types_reclamation_liste')]
    public function ReclamationTypes(Request $request, PersistenceManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Get the image associated with the user
        $image = $user->getImage();
        $recRepo = $entityManager->getRepository(TypeReclamation::class);
        $reclamations = $recRepo->findAll();
        return $this->render('admin/reclamation/listeTypeReclamation.html.twig', [
            // 'typeForm' =>$form->createView(),
            'image' => $image,
            'reclamations' => $reclamations,
        ]);
    }



// ................................................ Gesion Produits..................................................................................................... 

    #[Route('/liste_des_produits', name: 'app_products_list')]
    public function ListeProducts(Request $request,EntityManagerInterface $entityManager): Response
    {
            // Get the current user
            $user = $this->getUser();
            // Get the image associated with the user
            $image = $user->getImage();

            $prodRepo = $entityManager->getRepository(Produit::class);
            $produits = $prodRepo->findAll();

            
            

            return $this->render('admin/produit/listeProduit.html.twig', [
                'image' => $image,
                'produits' => $produits,
            ]);
    }

    #[Route('/liste_des_categories_produits', name: 'app_category_products_list')]
    public function ListeCatProducts(Request $request,EntityManagerInterface $entityManager): Response
    {
            // Get the current user
            $user = $this->getUser();
            // Get the image associated with the user
            $image = $user->getImage();

            $prodRepo = $entityManager->getRepository(Categorie::class);
            $categories = $prodRepo->findAll();

            // $reclamationType = new TypeReclamation();
            // $form = $this->createForm(ReclamationTypeType::class, $reclamationType);
            // $form->handleRequest($request);

            // if ($form->isSubmitted() && $form->isValid()) {
            //     $entityManager = $this->getDoctrine()->getManager();
            //     $entityManager->persist($reclamationType);
            //     $entityManager->flush();

            //     $this->addFlash('success', 'Reclamation type added successfully.');

            //     return $this->redirectToRoute('app_produits_list');
            // }
            

            return $this->render('admin/produit/listeCategoriesProduit.html.twig', [
                'image' => $image,
                'categories' => $categories,
                // 'typeForm' => $form->createView(),
            ]);
    }



// ................................................ Gesion commande..................................................................................................... 

    #[Route('/liste_des_commandes/{userc?}', name: 'app_commande_admin')]
    public function ListeCommande($userc): Response
    {
        // Get the current user
        $user = $this->getUser();
        // Get image associated with user
        $image = $user->getImage();
    
        
        //recuperer le repository
        $repository = $this->getDoctrine()->getRepository(Commande::class);
        //utiliser findAll() pour recuperer toutes les classes
        $commandes =$userc? $repository->findBy(['user' =>$userc ]) : $repository->findAll();

        return $this->render('admin/commande/ListeCommande.html.twig', [
            'commandes' => $commandes,
            'image' => $image
            
        ]);
    }


    #[Route('/liste_detail_commandes/{commande?}', name: 'app_detailcommande_admin')]
    public function ListedetailCommande($commande): Response
    {
        // Get the current user
        $user = $this->getUser();
        $image = $user->getImage();
    
        //recuperer le repository
        $repository = $this->getDoctrine()->getRepository(DetailCommande::class);
        //utiliser findAll() pour recuperer toutes les classes
        $details =$commande? $repository->findBy(['commande' =>$commande ]) : $repository->findAll();

        return $this->render('admin/commande/ListeDetails.html.twig', [
            'details' => $details,
            'image' => $image
            
        ]);
    }




}
