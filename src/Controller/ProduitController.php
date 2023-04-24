<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twilio\Rest\Client;
use Twig\Environment;
use Dompdf\Dompdf;



#[Route('/produit')]
class ProduitController extends AbstractController
{        
    


    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),

        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Client $client,Request $request, ProduitRepository $produitRepository, FlashyNotifier $flashy): Response
    {     

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('photo')->getData();
        $newFilename = uniqid().'.'.$imageFile->getClientOriginalExtension();
                 $uploadDir = $this->getParameter('kernel.project_dir') . '/public/productuploads';

            $imageFile->move($uploadDir,$newFilename);
            $produit->setPhoto($newFilename);

            $produitRepository->save($produit, true);
            $flashy->success('Article successfully created', 5000);
            $nom=$produit->getNom();
            /*$message = $client->messages->create(
                "+21621184125",
                [
                    'from' => '+16812025444',
                    'body' => "A new product is available in our shop product name : ".$nom,
                ]
            );*/
           /* $this->addFlash('success', 'Produit successfully created');*/

            

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
            
        }
       

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
    
          
        ]);
    }
    #[Route('/affichage', name: 'app_produit_affichage', methods: ['GET'])]
    public function indexFront(EntityManagerInterface $entityManager): Response
    {
        $categorie=$entityManager->getRepository(Categorie::class)->findAll();
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();

        return $this->render('produit/affichage.html.twig', [
            'categorie' => $categorie,
            'produits' => $produits,
        ]);
    }
    #[Route('/produit/{id?}/pdf', name: 'produit_pdf')]
    public function pdf(Produit $produit): Response
    {
        $options = new OptionsResolver();
        $options->setDefaults([
            'defaultFont' => 'Arial',
            'fontSize' => 12,
        ]);
        

        $dompdf = new Dompdf($options);

        $html = $this->renderView('pdf/index.html.twig', [
            'produit' => $produit,
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }



    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'categorie_path' => $this->generateUrl('app_categorie_index', ['id' => $produit->getCategorie()->getId()])
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository,FlashyNotifier $flashy): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);
            $flashy->success('Article successfully updated', 5000);

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository,FlashyNotifier $flashy): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
            $flashy->success('Article successfully deleted', 5000);
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }



   /* #[Route('/{id}/pdf', name: 'app_produit_pdf', methods: ['GET'])]
    public function pdf(Produit $produit, Environment $twig,ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();
        $html = $twig->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="produit.pdf"',
            'produits' => $produitRepository->findAll(),
        ]);
    }*/
}

   
   