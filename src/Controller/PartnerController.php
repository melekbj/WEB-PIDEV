<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Store;
use App\Form\UserType;
use App\Entity\Produit;
use App\Form\StoreType;
use Twilio\Rest\Client;
use App\Form\ProductType;
use App\Entity\CategorieStore;
use App\Entity\DetailCommande;
use App\Service\SendSmsService;
use App\Repository\StoreRepository;
use App\Repository\RatingRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CategorieStoreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Handler\UploadHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;





#[Route('/partner')]
class PartnerController extends AbstractController
{
    // private UploadHandler $uploadHandler;

    // public function __construct(UploadHandler $uploadHandler)
    // {
    //     $this->uploadHandler = $uploadHandler;
    // }

    
    // #[Route('/', name: 'app_partner')]
    // public function index(): Response
    // {
    //     $user = $this->getUser();
    //     // Get the image associated with the user
    //     $image = $user->getImage();
    //     return $this->render('partner/index.html.twig', [
    //         'controller_name' => 'PartnerController',
    //         'image' => $image,
    //         'user' => $user
    //     ]);
    // }


// .........................................Gestion Store..........................................................
   
#[Route('/', name: 'app_partner')]
public function index(StoreRepository $storeRepository, Security $security): Response
{
        
        $user = $security->getUser();
        $store = $storeRepository->findStoreByUserId($user->getId());

        if ($store !== null) {
            return $this->redirectToRoute('app_store_show_partner', [
                'id' => $store->getId(),
                'user' => $user,
                // 'image' => $image
            ]);
        } else {
            // Handle the case where the user doesn't have a store
            // For example, you could redirect them to the new store page
            return $this->redirectToRoute('app_store_new_partner');
        }
}


#[Route('/new/store', name: 'app_store_new_partner', methods: ['GET', 'POST'])]
public function newStore(Request $request,Security $security): Response
{
    $user = $this->getUser();
    // Get the image associated with the user
    $image = $user->getImage();

    $store = new Store();
    $user = $security->getUser();
    $form = $this->createForm(StoreType::class, $store);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $store->setUser($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($store);
        $entityManager->flush();
        $this->addFlash('success', 'Store created successfully!');
        return $this->redirectToRoute('app_store_show_partner', ['id' => $store->getId()]);
        

    }

    return $this->render('partner/store/new.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
        'image' => $image,
    ]);
}

#[Route('/show/{id?}', name: 'app_store_show_partner')]
public function showStore(?int $id, StoreRepository $storeRepository, RatingRepository $ratingRepository): Response
{//TODO: add the rating methode and test if it is first time create a new insert else do an edit 
    $user = $this->getUser();
    $image = $user->getImage();  
    if ($id === null) {
        return $this->redirectToRoute('app_store_new_partner');
    } else {
        $store = $storeRepository->find($id);
        $rating = $ratingRepository->getAverageStoreRating($id);
        // addflash

        // $this->addFlash('success', 'Store updated successfully!');
        return $this->render('partner/store/show.html.twig', [
        'store' => $store,
        'rating' => $rating,
        'user' => $user,
        'image' => $image
        ]);
    }
}

#[Route('/edit/{id}', name: 'app_store_edit_partner', methods: ['GET', 'POST'])]
public function editStore(Request $request, Store $store, StoreRepository $storeRepository): Response
{
    $user = $this->getUser();
    // Get the image associated with the user
    $image = $user->getImage();

    $form = $this->createForm(StoreType::class, $store);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $storeRepository->save($store, true);
        $this->addFlash('success', 'Store updated successfully!');

        return $this->redirectToRoute('app_partner', [], Response::HTTP_SEE_OTHER);

    }

    return $this->renderForm('partner/store/edit.html.twig', [
        'store' => $store,
        'form' => $form ,
        'user' => $user,  
        'image' => $image,    
    ]);
}
    
// .........................................Gestion Product Store..........................................................

    
    #[Route('/new/produit/{id}', name: 'app_product_new')]
    public function newProductinStore(Request $request, PersistenceManagerRegistry $doctrine, Security $security,$id): Response
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();
        $store = $entityManager->getRepository(Store::class)->find($id);

        if (!$store) {
            throw $this->createNotFoundException('Store with ID '.$id.' not found');
        }

        $product = new Produit();
        $product->setEtat(0);
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store->addProduit($product);
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Product created successfully!');
            return $this->redirectToRoute('app_products_store_liste');
        }

       

   

        return $this->render('partner/produit/addProduit.html.twig', [
        'form' => $form->createView(),
        'user' => $user
        ]);
    }


    #[Route('/products_store/liste', name: 'app_products_store_liste')]
    public function productsinStore(Request $request, PersistenceManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $store = $doctrine->getManager()->getRepository(Store::class)->findOneBy(['user' => $user]); 
        //get products by in the store 
        
        $products = $store->getProduit();
        


        return $this->render('partner/ListProductInStore.html.twig', [
            'produits' => $products,
            'store' => $store,
            'user' =>$user
        ]);
    }

// .........................................Gestion Commande..........................................................


    #[Route('/commands', name: 'app_par_commands')]
    public function partnercommande(Request $request, ManagerRegistry $doctrine): Response
    {
        $user=$this->getUser();
        $store = $doctrine->getRepository(Store::class)->findBy(['user'=>$user->getId()]);
        // Get the query parameters from the URL
        $commandedetail = $request->query->get('commandedetail');
        $displaydetail = null;
        // neeeds to completed the prix filter  gonna update and try few things and comeback to this wael3
        if ($commandedetail !== null && ($etatswitch === "Completed" || $etatswitch === "Pending"  || $etatswitch === "Progress")) {
            $detail = $doctrine->getRepository(DetailCommande::class)->find($commandedetail);
            $doctrine->getManager()->persist($detail);
            $doctrine->getManager()->flush();
            $entityManager = $doctrine->getManager();

            // Get the original commande
            $originalCommande = $detail->getCommande();

            // Get all details associated with the original commande
            $details = $doctrine->getRepository(DetailCommande::class)->findBy(['commande' => $originalCommande]);

            // Determine the minimum etat among all details
            $countPending = 0;
            $countProgress = 0;
            $countCompleted = 0;
            foreach ($details as $d) {
                if ($d->getEtat() === "Pending") {
                    $countPending++;
                }
                if ($d->getEtat() === "Completed" || $d->getEtat() === "Canceled" ) {
                    $countCompleted++;;
                }
            }

            // Update the etat of the original commande if the minimum etat is less than the current etat
            $currentEtat = $originalCommande->getEtat();
            
                if ($countProgress === 0 && $countPending === 0) {
                    
                        $originalCommande->setEtat("Completed");
                        $entityManager->persist($originalCommande);
                        $entityManager->flush();
                    
                } else {
                    $originalCommande->setEtat("Progress");
                    $entityManager->persist($originalCommande);
                    $entityManager->flush();
                }
            
        }

        $displaydetail = $doctrine->getRepository(DetailCommande::class)->findByStore($store, $etat, $order);


        return $this->render('partner/commands.html.twig', [
            //     'historiquecommande' => $commande,
            'selecteddetails' => $displaydetail,
            'testinput' => $commandedetail,
            'user' => $user,
        ]);
    }
























}
