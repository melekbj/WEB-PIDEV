<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    
    #[Route('/register', name: 'app_registration')]
    public function registration(Request $request, PersistenceManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHashed): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHashed->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            if ($user->getRoles() == 'ROLE_PARTNER') {
                $user->setEtat(1);
            }
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $request->getSession()->getFlashBag()->add('success', 'Account created successfully.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('register/index.html.twig', [
            'registerForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // customize the error message for bad credentials
        if ($error instanceof BadCredentialsException) {
            $error = 'Invalid email or password. Please try again.';
        }

        return $this->render('authentication/login.html.twig', [
            'error' => $error,
        ]);
    }

 
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }










}
