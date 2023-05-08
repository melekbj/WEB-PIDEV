<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ProduitControllerAPI extends AbstractController
{

    private $entityManager;
    private $produitRepository;

    public function __construct(EntityManagerInterface $entityManager, ProduitRepository $produitRepository)
    {
        $this->entityManager = $entityManager;
        $this->produitRepository = $produitRepository;
    }
  
    #[Route('/getprodJSON', name: 'getprodJSON', methods: ['GET', 'POST'])]
    public function myApi(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Produit::class);
        $data = $repository->createQueryBuilder('e')
            ->select('e.id, e.nom, e.prix, e.quantite, e.etat' )
            ->getQuery()
            ->getArrayResult();
  
        return $this->json($data);
    }


    #[Route('/addprodJSON', name: 'addprodJSON', methods: ['GET', 'POST'])]
    public function addProdJSON( Request $request ,NormalizerInterface $normalizer ){
        $em=$this->getDoctrine()->getManager();
        $produit=new Produit();
        $produit->setNom($request->get('nom'));
        $produit->setPrix($request->get('prix'));
        $produit->setQuantite($request->get('quantite'));
        $produit->setEtat($request->get('etat'));
      
   
      
        $em -> persist($produit);
        $em->flush();
        $jsonContent=$normalizer->normalize($produit, 'json', ['circular_reference_handler' => function ($object) {
            return $object->getId();
        }, 'max_depth' => 1]);
        return new Response("produit ajoutéé".json_encode($jsonContent));
  
    }


    
  #[Route('/editProdJSON', name: 'editProdJSON', methods: ['GET','POST'])]
  public function editProdJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $produit = $em->getRepository(Produit::class)->find($id);
  
      if (!$produit) {
          return new Response("produit with id $id not found");
      }
  
      $produit->setNom($request->get('nom'));

      $produit->setPrix($request->get('prix'));
      $produit->setQuantite($request->get('quantite'));
      $produit->setEtat($request->get('etat'));
   
    
      $em -> persist($produit);
      $em->flush();
  
      $jsonContent = $normalizer->normalize($produit, 'json', [
          'circular_reference_handler' => function ($object) {
              return $object->getIdUser();
          },
          'max_depth' => 1
      ]);
      return new Response("produit updated" . json_encode($jsonContent));
  }
  





  #[Route('/jsonproddelete', name: 'jsonproddelete', methods: ['POST', 'DELETE'])]
  public function deleteprodJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $produit = $em->getRepository(Produit::class)->find($id);
  
      if ($request->isMethod('DELETE')) {
          $em->remove($produit);
          $em->flush();
          return new Response("produit deleted");
      } else {
          $em->persist($produit);
          $em->flush();
          $jsonContent = $normalizer->normalize($produit, 'json', [
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
              },
              'max_depth' => 1
          ]);
          return new Response("produit updated" . json_encode($jsonContent));
      }
  }
  
}
