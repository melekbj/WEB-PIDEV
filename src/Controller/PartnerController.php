<?php

namespace App\Controller;

use App\Entity\DetailCommande;
use App\Entity\Store;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartnerController extends AbstractController
{
    #[Route('/partner', name: 'app_partner')]
    public function index(): Response
    {
        return $this->render('partner/index.html.twig', [
            'controller_name' => 'PartnerController',
        ]);
    }
    #[Route('/commands', name: 'app_par_commands')]
    public function partnercommande(Request $request, ManagerRegistry $doctrine): Response
    {
         $partner=$this->getUser();
        $store = $doctrine->getRepository(Store::class)->findBy(['user'=>$partner->getId()]);
        // Get the query parameters from the URL
        $etat = $request->query->get('etat');
        $order = $request->query->get('prixOrder');
        $etatswitch = $request->query->get('etatswitch');
        $commandedetail = $request->query->get('commandedetail');
        $displaydetail = null;
        // neeeds to completed the prix filter  gonna update and try few things and comeback to this wael3
        // Get the commandes and details from the database

        //  $commande = $doctrine->getRepository(Commande::class)->findByStore($client,$etat,$min,$max,$order);
        if ($commandedetail !== null && ($etatswitch === "Completed" || $etatswitch === "Pending"  || $etatswitch === "Progress")) {
            $detail = $doctrine->getRepository(DetailCommande::class)->find($commandedetail);

            $detail->setEtat($etatswitch);
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
                if ($d->getEtat() === "Progress") {
                    $countProgress++;
                }
                if ($d->getEtat() === "Completed") {
                    $countCompleted++;;
                }
            }

            // Update the etat of the original commande if the minimum etat is less than the current etat
            $currentEtat = $originalCommande->getEtat();
            
                if ($countProgress === 0) {
                    if ($currentEtat === "Progress") {
                        $originalCommande->setEtat("Completed");
                        $entityManager->persist($originalCommande);
                        $entityManager->flush();
                    }
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
        ]);
    }

}
