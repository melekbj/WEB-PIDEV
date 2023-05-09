<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Entity\TypeReclamation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
class ReclamationController extends AbstractController
{
    #[Route('/addReclamationProduit', name: 'app_reclamation')]
    public function addAction(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, int $userId = null): Response
    {
        $reclamation = new Reclamation();
        $typeReclamation = $entityManager->getRepository(TypeReclamation::class)->findOneBy(['nom' => 'Produit']);
        $reclamation->setEtat('pending'); // Set default value for etat
        $reclamation->setType($typeReclamation); // Set default value for type
        $reclamation->setDate(new \DateTime());

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_reclamation');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            
            $reclamation->setDescription($form->get('description')->getData());
     
            $image = $form->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('reclamation_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                 $message = 'An error occurred while uploading the file: ' . $e->getMessage();
                $session->getFlashBag()->add('error', $message);

                // Redirect back to the form
                return $this->redirectToRoute('app_reclamation');                }

                $reclamation->setimage($newFilename);
            }
            $user = $this->getUser();
if ($user) {
    $reclamation->setClientId($user->getId());
}
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Reclamation added successfully!');
            return $this->redirectToRoute('app_reclamation');
        }

        return $this->render('reclamation/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('add_reclamation/{idUser}/{idCommande}/{etat}/{date}/{image}/{contenu}/{idProduit}/{idAdmin}/{type}', name: 'addreclamation')]
    public function addReclamation(Request $request, EntityManagerInterface $entityManager,NormalizerInterface $Normalizer)
    {
        $reclamation = new Reclamation();
        $reclamation->setClientId($request->get('idUser'));
        $reclamation->setEtat($request->get('etat'));
        $reclamation->setDate(new \DateTime($request->get('date')));
        $reclamation->setDescription($request->get('contenu'));
        $reclamation->setimage($request->get('image'));
        echo('aaaaaaaaaaaaaaaaaaaa');
        $typeId = $request->get('type');
        $typeReclamation = $entityManager->getRepository(TypeReclamation::class)->find($typeId);
        $reclamation->setType($typeReclamation);        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($reclamation);
        $entityManager->flush();
            $jsonContent = $Normalizer->normalize($reclamation, 'json', ['groups' => 'reclamations']);
        return new Response(json_encode($jsonContent));
    }

    #[Route("/getReclamations", name: "getReclamations")]
    public function getReclamations(SerializerInterface $serializer, NormalizerInterface $normalizer): Response
    {
        $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findAll();
        $reclamationsNormalises = $normalizer->normalize($reclamations, 'json', ['groups' => "reclamation"]);
        $json = json_encode($reclamationsNormalises);
        // $json = $serializer->serialize($reclamationsNormalises, 'json', ['groups' => "reclamations"]);
        return new Response($json);
    }
    
    #[Route("/updateReclamation/{id}/{etat}", name: "updateReclamations")]
    public function updateReclamation($id, $etat): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        // Find the Reclamation entity by its id
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
        
        if (!$reclamation) {
            throw $this->createNotFoundException(
                'No reclamation found for id '.$id
            );
        }
        
        // Update the etat property of the Reclamation entity
        $reclamation->setEtat($etat);
        
        // Save the updated entity to the database
        $entityManager->flush();
        
        return new Response('Reclamation with id '.$id.' updated with etat '.$etat);
    }
    #[Route('/add_type_reclamation/{type}', name: 'addreclamationType')]
    public function addReclamationType($type, EntityManagerInterface $entityManager, NormalizerInterface $normalizer) {
        $typeReclamation = new TypeReclamation();
        $typeReclamation->setNom($type);
        $entityManager->persist($typeReclamation);
        $entityManager->flush();
        $jsonContent = $normalizer->normalize($typeReclamation, 'json');
        return new Response(json_encode($jsonContent));
    }
    #[Route("/getReclamationTypes", name: "getReclamationTypes")]
public function getReclamationTypes(SerializerInterface $serializer, NormalizerInterface $normalizer): Response
{
    $types = $this->getDoctrine()->getRepository(TypeReclamation::class)->findAll();
    $typesNormalizes = $normalizer->normalize($types, 'json');
    $json = json_encode($typesNormalizes);
    // $json = $serializer->serialize($typesNormalizes, 'json');
    return new Response($json);
}

#[Route("/getReclamationbyId/{id}", name: "getReclamationsbyId")]
public function getReclamationbyId(SerializerInterface $serializer, NormalizerInterface $normalizer,$id): Response
{
    $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findBy(['client_id' => $id]);
    $reclamationsNormalises = $normalizer->normalize($reclamations, 'json', ['groups' => "reclamation"]);
    $json = json_encode($reclamationsNormalises);
    // $json = $serializer->serialize($reclamationsNormalises, 'json', ['groups' => "reclamations"]);
    return new Response($json);
}
}
