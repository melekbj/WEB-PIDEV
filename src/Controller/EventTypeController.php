<?php

namespace App\Controller;

use App\Entity\EventType;
use App\Form\EventTypeType;
use App\Repository\EventTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Evenement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


#[Route('/event/type')]
class EventTypeController extends AbstractController
{
    #[Route('/', name: 'app_event_type_index', methods: ['GET'])]
    public function index(EventTypeRepository $eventTypeRepository): Response
    {
        return $this->render('event_type/index.html.twig', [
            'event_types' => $eventTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EventTypeRepository $eventTypeRepository): Response
    {
        $eventType = new EventType();
        $form = $this->createForm(EventTypeType::class, $eventType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $eventTypeRepository->save($eventType, true);

                return $this->redirectToRoute('app_event_type_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'This libelle already exists.');
                // Render the form again with the error message
                return $this->renderForm('event_type/new.html.twig', [
                    'event_type' => $eventType,
                    'form' => $form,
                ]);
            }
           
        }

        return $this->renderForm('event_type/new.html.twig', [
            'event_type' => $eventType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_type_show', methods: ['GET'])]
    public function show(EventType $eventType): Response
    {
        return $this->render('event_type/show.html.twig', [
            'event_type' => $eventType,
        ]);
    }

   #[Route('/{id}/edit', name: 'app_event_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EventType $eventType, EventTypeRepository $eventTypeRepository,$id): Response
    {
        $form = $this->createForm(EventTypeType::class, $eventType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventTypeRepository->save($eventType, true);

            return $this->redirectToRoute('app_event_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event_type/edit.html.twig', [
            'event_type' => $eventType,
            'form' => $form,
            'id'=>$id,
        ]);
    }
 



    #[Route('/{id}', name: 'app_event_type_delete', methods: ['POST'])]
    public function delete(Request $request, EventType $eventType, EventTypeRepository $eventTypeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$eventType->getId(), $request->request->get('_token'))) {
            $eventTypeRepository->remove($eventType, true);
        }

        return $this->redirectToRoute('app_event_type_index', [], Response::HTTP_SEE_OTHER);
    }
    

   
#[Route('/removelibelle/{id} ', name: 'app_event_delete_page')]
public function deleteLibelle(Request $request,ManagerRegistry $doctrine,EntityManagerInterface $em , $id)
{
   // récupère la variable "response" passée dans l'URL

    $response=$request->query->get('response');
    $response2=$request->query->get('newlibelleid');

    if ($response === "Annuler") {
        return $this->redirectToRoute('app_event_type_index');
    } else {
        if($response==="Modifier"){
            // Event Type to delete
        $eventLibelle = $doctrine->getRepository(EventType::class)->find($id);
            // On récupère tous les événements qui ont cet événement libellé
            $eventsToChange = $doctrine->getRepository(Evenement::class)->findBy(['type' => $eventLibelle]);

            // la nouvelle Even Type a affeceter
            $newEventLibelle = $doctrine->getRepository(EventType::class)->find($response2);
            foreach ($eventsToChange as $event) {
                $em->persist($event);
                $event->setType($newEventLibelle);
            }
            $em->remove($eventLibelle);
           $em->flush();
            return $this->redirectToRoute('app_event_type_index');
        
        } else {
            // On récupère l'événement correspondant à l'ID
            if ($response==="Cascade"){

                // event type a supprimer 
                $eventLibelle = $doctrine->getRepository(EventType::class)->find($id);
                
                    // On supprime l'événement de la base de données, ce qui déclenchera la suppression en cascade des événements liés
                    $eventsToRemove = $doctrine->getRepository(Evenement::class)->findBy(['type' => $eventLibelle]);
                    foreach ($eventsToRemove as $event) {
                        $em->remove($event);
                    }
                    $em->remove($eventLibelle);
                    $em->flush();
                    return $this->redirectToRoute('app_event_type_index');
            }
        }
    }
    

    $Libelle=$doctrine->getRepository(EventType::class)->find($id);
    $list=$doctrine->getRepository(EventType::class)->findAll();
    return $this->renderForm('event_type/deleteCate.html.twig', [
        'Libelle' => $Libelle,
        'list' => $list,
        'id'=>$id,
    ]);
}

    
   
}
