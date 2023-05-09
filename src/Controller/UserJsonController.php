<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserJsonController extends AbstractController
{
   


    #[Route('/listeUsersJson', name: 'liste_users')]
    public function EventsTypes(
        Request $request,
        NormalizerInterface $normalizer, 
        PersistenceManagerRegistry $doctrine, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $users = $userRepo->findAll();
        $usersNormalises = $normalizer->normalize($users, 'json', ['groups'=>"addUser"]);
        $usersJson = json_encode($usersNormalises);
        $response = new Response($usersJson);

        return $response;
    }

    #[Route('/registerJson', name: 'app_registration_json')]
    public function registration(
        Request $request,
        PersistenceManagerRegistry $doctrine, 
        UserPasswordHasherInterface $passwordHashed,
        NormalizerInterface $normalizer
    ): Response
    {
        
        $em = $doctrine->getManager();
        $user = new User();

        $password = $passwordHashed->hashPassword(
            $user,
            $request->get('password')
        );
        $user
            ->setEmail($request->get('email'))
            ->setPassword($password)
            ->setRoles($request->get('roles'))
            ->setNom($request->get('nom'))
            ->setPrenom($request->get('prenom'))
            ->setAdresse($request->get('adresse'))
            ->setPhone($request->get('phone'))
            ->setGenre($request->get('genre'))
            ->setVille($request->get('ville'))
            ->setAge($request->get('age'))
            ->setImage($request->get('image'));

        $em->persist($user);
        $em->flush();
        $jsonContent = $normalizer->normalize($user, 'json', ['groups' => 'addUser']);
        return new Response("User added successfully" . json_encode($jsonContent));

    }


    #[Route('/deleteUserJson/{id}', name: 'app_delete_user_json')]
    public function deleteUser(
        Request $request,
        PersistenceManagerRegistry $doctrine,
        NormalizerInterface $normalizer,
        int $id
    ): Response {
        $em = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $em->remove($user);
        $em->flush();

        $usersNormalises = $normalizer->normalize($user, 'json', ['groups'=>"addUser"]);
        return new Response("User deleted successfully" . json_encode($usersNormalises));
    }

    #[Route('/updateUserJson/{id}', name: 'app_update_user_json')]
    public function updateUser(
        Request $request,
        PersistenceManagerRegistry $doctrine,
        NormalizerInterface $normalizer,
        int $id
    ): Response {
        $em = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $user->setNom($request->get('nom'));
        // $user->setEmail();

        $em->flush();

        // Normalize and return updated user data in JSON format
        $userNormalizes = $normalizer->normalize($user, 'json', ['groups' => 'addUser']);
        return new Response("User updated successfully" . json_encode($userNormalizes));
    }


    #[Route('/loginJson', name: 'app_login_json')]
    public function login(Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository): JsonResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');

        // Find user by email
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid email or password.'], 401);
        }

        // Check if password is correct
        if (!$passwordEncoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid email or password.'], 401);
        }

        // Authentication successful, return user information
        $userData = [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'adresse' => $user->getAdresse(),
            'phone' => $user->getPhone(),
            'genre' => $user->getGenre(),
            'ville' => $user->getVille(),
            'age' => $user->getAge()
        ];

        return new JsonResponse(['message' => 'Authentication successful.', 'user' => $userData], 200);
    }











}
