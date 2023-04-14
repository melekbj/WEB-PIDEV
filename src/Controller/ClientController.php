<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\PaymentType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Card;
use Stripe\Customer;
use Stripe\Token;

class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }
     

    #[Route('/historique', name: 'app_historique')]
    public function historique(Request $request, ManagerRegistry $doctrine, CommandeRepository $cc): Response
    {
         $user=$this->getUser();
         $client=$user->getId();
         // $client = '1';
        // Get the query parameters from the URL
        $etat = $request->query->get('etat');
        $order = $request->query->get('prixOrder');
        $min = $request->query->get('min');
        $max = $request->query->get('max');
        $commandedetail = $request->query->get('commandedetail');
        $displaydetail = null;
        // neeeds to completed the prix filter  gonna update and try few things and comeback to this wael3
        // Get the commandes and details from the database

        // $commande = $doctrine->getRepository(Commande::class)->findByClientByEtatByPrice2($client, $etat, $min, $max, $order);
        $commande = $cc->findByClientByEtatByPrice2($client, $etat, $min, $max, $order);

        $displaydetail = $doctrine->getRepository(DetailCommande::class)->findByCommande($commandedetail);

        return $this->render('client/historique.html.twig', [
            'historiquecommande' => $commande,
            'selecteddetails' => $displaydetail,
            'testinput' => $commandedetail,
        ]);
    }
    // loads a list of product for safa ( just need the button )
    #[Route('/products', name: 'app_viewproduit')]
    public function listview(ManagerRegistry $doctrine): Response
    {

        $produits = $doctrine->getRepository(Produit::class)->findAll();


        return $this->render('home/products.html.twig', [
            'produits' => $produits,
        ]);
    }


    #[Route('/cart/add/{id}', name: 'app_cart_addscript')]
    public function listviewscript($id, SessionInterface $session, ManagerRegistry $doctrine, Request $request): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            // makes sure to not execced the limit of availeble quantity
            $produit = $doctrine->getRepository(Produit::class)->find($id);
            if ($produit->getQuantite() > $cart[$id]) {

                $cart[$id]++;
            } else {
                $session->set('carterror', 'you have exceeded the availeble quantity');
            }
        } else {
            $cart[$id] = 1;
        }
        $session->set('cart', $cart);
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);



        //------test purpose to clear all stored items inside the session--------
        //   $session->clear();
        //---------test session stored item cart----------
        //  dd($session->get('cart'));


    }
    #[Route('/cart/remove/{id}', name: 'app_cart_removescript')]
    public function listviewscript2($id, SessionInterface $session, ManagerRegistry $doctrine, Request $request): Response
    {
        $cart = $session->get('cart', []);
        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);



        //------test purpose to clear all stored items inside the session--------
        //   $session->clear();
        //---------test session stored item cart----------
        //  dd($session->get('cart'));


    }
    // test purpose dint work ,, not deleting it might need it  could contain                                                           {{ render(controller('App\\Controller\\ClientController::fillcart')) }}
    //  {{ render(controller('App\\Controller\\ClientController::fillcart')) }} in base.html.twig

    // afficher la panier courrante cart courante 
    #[Route('/cartview', name: 'app_cartview')]
    public function cartview(Request $request, ManagerRegistry $doctrine, SessionInterface $session): Response
    {

        // Get the session storage data sent in the request
        $cart = $session->get('cart', []);
        $error = $session->get('carterror', '');
        $session->remove('carterror');
        // Get the cart data from the session object

        // Retrieve the products from the database based on the cart data
        $produits = [];
        foreach ($cart as $itemId => $quantity) {
            $produit = $doctrine->getRepository(Produit::class)->find($itemId);
            if ($produit) {
                $produits[] = [
                    'produit' => $produit,
                    'quantity' => $quantity,
                ];
            }
        }

        // Render the cart view template with the product data
        return $this->render('client/cartview.html.twig', [
            'produits' => $produits,
            'error' => $error,
        ]);
    }
    // button ajouter commadne
    #[Route('/cart/confirm', name: 'app_cart_confirm')]
    public function confirmercart(EntityManagerInterface $em, SessionInterface $session, Request $request)
    {
       // $client = '1';
        $user=$this->getUser();
      //  $client=$user->getId();
     //   $user = $em->getRepository(User::class)->find($client);

        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $session->set('carterror', 'cart is empty.');
            return $this->redirectToRoute('app_cartview');
        }

        $totalPrice = 0;
        $command = new Commande();
        $command->setUser($user);
        $command->setDestination($request->request->get('destination'));
        if (strlen($request->request->get('destination')) <= 10) {
            $session->set('carterror', 'Destination not set or too short.');
            return $this->redirectToRoute('app_cartview');
        }
        $command->setDate(new \DateTime());
        $command->setEtat('Pending');
        foreach ($cart as $productId => $quantity) {
            $product = $em->getRepository(Produit::class)->find($productId);
            if ($product->getQuantite() < $quantity) {
                $session->set('carterror', 'some products may not have the correct amount of availeble quantitys');
                return $this->redirectToRoute('app_cartview');
                break;
            }
            $price = $product->getPrix() * $quantity;
            $totalPrice += $price;
            $detailCommande = new DetailCommande();
            $detailCommande->setCommande($command);
            $detailCommande->setProduit($product);
            $detailCommande->setStore($product->getStores()->first());
            $detailCommande->setQuantite($quantity);
            $detailCommande->setPrixTotal($price);
            $detailCommande->setEtat('Pending');
        }

        $command->setPrix($totalPrice);


        $session->set('commande', $command);

        if (!empty($cart) && strlen($request->request->get('destination')) > 10) {
            return $this->redirectToRoute('app_cart_payement');
        }


        return $this->redirectToRoute('app_historiquesecond');
    }

    #[Route('/cart/payment', name: 'app_cart_payement')]
    public function Payementprocess(EntityManagerInterface $em, SessionInterface $session, Request $request, ManagerRegistry $doctrine)
    {

      //  $client = '1';
      $user=$this->getUser();
   //   $client=$user->getId();
   //     $user = $em->getRepository(User::class)->find($client);

        $cart = $session->get('cart', []);

        foreach ($cart as $itemid => $quantity) {
            $produit = $doctrine->getRepository(Produit::class)->find($itemid);
            $produits[] = [
                'produit' => $produit,
                'quantity' => $quantity,
            ];
        }
        $command = $session->get('commande');
        $paymentForm = $this->createForm(PaymentType::class);
        $paymentForm->handleRequest($request);
        if ($paymentForm->isSubmitted() && $paymentForm->isValid()) {
            $paymentData = $paymentForm->getData();
           
            // Set your Stripe API secret key
            Stripe::setApiKey('sk_test_51Mf0S6FwJ7wXIwXewSc2z6FyXoFWAJZFy0Iuk4OZxzTVzLENEvBnnqug21baEIiV0MEDXTYl0y4Ajnp2LDWRZtC300mrwZe2j2');
            // create a card object with the informations
            $card = new Card();
            $card->number = $paymentData['cardNumber'];
            $card->exp_month = $paymentData['expirationMonth'];
            $card->exp_year = $paymentData['expirationYear'];
            $card->cvc = $paymentData['cvc'];
            $card->address_zip = '12345';
            // Create a new Stripe customer
            // Create a new Stripe token from the card details
            $token = Token::create([
                'card' => [
                    'number' => $card->number,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                    'cvc' => $card->cvc,
                    'address_zip' => $card->address_zip,
                ],
            ]);
            // Create a new Stripe customer
            $customer = Customer::create([
                'email' => $user->getEmail(),
                'source' => $token->id, // pass the token ID as the value of 'source'
            ]);

            // Create a new Stripe charge
            try {
                $charge = Charge::create([
                    'amount' => $command->getPrix() * 100, // amount in cents
                    'currency' => 'usd', // or 'eur', 'gbp', etc.
                    'description' => 'My Awesome Ecommerce Payment',
                    'customer' => $customer->id, // add the customer to the charge
                ]);

                // on sucess 
                $totalPrice = 0;
                $command = $session->get('commande');
                $em->persist($command);
                foreach ($cart as $productId => $quantity) {
                    $product = $em->getRepository(Produit::class)->find($productId);
                    $price = $product->getPrix() * $quantity;
                    $product->setQuantite($product->getQuantite() - $quantity);
                    $em->persist($product);
                    $detailCommande = new DetailCommande();
                    $detailCommande->setCommande($command);
                    $detailCommande->setProduit($product);
                    $detailCommande->setStore($product->getStores()->first());
                    $detailCommande->setQuantite($quantity);
                    $detailCommande->setPrixTotal($price);
                    $detailCommande->setEtat('Pending');
                    $em->persist($detailCommande);
                }    
                
                $command->setUser($user);
                $em->flush();  // only after the charge is succesfull
                $session->remove('commande');
                $session->remove('cart');
            } catch (\Stripe\Exception\CardException $e) {
                // The card has been declined
                $session->set('carderror', $e->getMessage());
                return $this->redirectToRoute('app_cart_payement');
            }




            // if payment is sucess to redirect
            return $this->redirectToRoute('app_historique');
        }
        $error = $session->get('carderror' | '');
        return $this->render('client/Payment.html.twig', [
            'produits' => $produits,
            'error' => $error,
            'paymentForm' => $paymentForm->createView(),
            'command' => $command,
        ]);
    }

}
