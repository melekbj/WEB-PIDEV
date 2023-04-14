<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
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


    #[Route('/liste_des_utilisateurs/{id?}', name: 'app_users')]
    public function ListeU($id): Response
    {
        // Get the current user
        $user = $this->getUser();
        if ($id !== null)
        {
            $users= $this->getDoctrine()->getRepository(User::class)->findBy(['id'=>$id]);
            return $this->render('admin/ListeUsers.html.twig', [
                'users' => $users,
                
            ]);
            

        }
        
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


    #[Route('/liste_des_commandes/{userc?}', name: 'app_commande_admin')]
    public function ListeCommande($userc): Response
    {
        // Get the current user
        $user = $this->getUser();
     
        
        //recuperer le repository
        $repository = $this->getDoctrine()->getRepository(Commande::class);
        //utiliser findAll() pour recuperer toutes les classes
        $commandes =$userc? $repository->findBy(['user' =>$userc ]) : $repository->findAll();

        return $this->render('admin/ListeCommande.html.twig', [
            'commandes' => $commandes,
            
        ]);
    }


    #[Route('/liste_detail_commandes/{commande?}', name: 'app_detailcommande_admin')]
    public function ListedetailCommande($commande): Response
    {
        // Get the current user
        $user = $this->getUser();
       
        //recuperer le repository
        $repository = $this->getDoctrine()->getRepository(DetailCommande::class);
        //utiliser findAll() pour recuperer toutes les classes
        $details =$commande? $repository->findBy(['commande' =>$commande ]) : $repository->findAll();

        return $this->render('admin/ListeDetails.html.twig', [
            'details' => $details,
            
        ]);
    }

   

    


    


    

    
    



}
