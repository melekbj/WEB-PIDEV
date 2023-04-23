<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Evenement;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $eventrepo = $entityManager->getRepository(Evenement::class);
        $events = $eventrepo->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'events' => $events,
        ]);
    }

    #[Route('/products', name: 'app_products')]
    public function produitIndex(): Response
    {
        return $this->render('home/products.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('products/details', name: 'app_detail')]
    public function storeIndex(): Response
    {
        return $this->render('home/detail.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function aboutIndex(): Response
    {
        return $this->render('home/about.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contactIndex(): Response
    {
        return $this->render('home/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }







}
