<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EvenementType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Dompdf\Dompdf;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\Writer\DataUriWriter;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;







#[Route('/evenement')]
class EvenementController extends AbstractController
{

    
    
    #[Route('/', name: 'app_events_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EvenementRepository $evenementRepository): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evenementRepository->save($evenement, true);

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EvenementRepository $evenementRepository): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evenementRepository->save($evenement, true);

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EvenementRepository $evenementRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $evenementRepository->remove($evenement, true);
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/detailEvent/{id}', name: 'app_evenement_index', methods: ["GET", "POST"] )]
    public function showev($id, EvenementRepository $rep, Request $request): Response
    {
        //Utiliser find by id
        $evenement = $rep->find($id);
        return $this->render('evenement/index.html.twig', [
            
            'evenement' => $evenement,
        ]);

        }

       

//     #[Route('/evenement/{id?}/qrcode', name: 'app_evenement_qrcode')]
//     public function qrcode(Evenement $evenement): Response
//     {
//         $this->qrCode->setText('http://example.com')
//                       ->setSize(300)
//                       ->setPadding(10)
//                       ->setErrorCorrection('high')
//                       ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
//                       ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
//                       ->setLabel('My label')
//                       ->setLabelFontSize(16)
//                       ->setImageType(QrCode::IMAGE_TYPE_PNG);

//         $qrCodeImg = $this->qrCode->writeString();

//         return $this->render('pdf/index.html.twig', [
//             'evenement' => $evenement,
//             'qrCodeImg' => $qrCodeImg,
//         ]);
    
// }
        
      #[Route('/evenement/{id?}/pdf', name: 'app_evenement_pdf')]
      public function pdf(Evenement $evenement, UrlGeneratorInterface $urlGenerator,$id,EvenementRepository $rep): Response
      {
        
          // Générer le QR code en utilisant l'URL de la route qr_code pour l'événement en cours
    //       $qrCodeUrl = $urlGenerator->generate('qr_code', ['id' => $evenement->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    //       $qrCodeImg = '<img src="'.$qrCodeUrl.'" />';
    //       $qrCode = $this->get(QrCodeGeneratorInterface::class)
    //       ->generate('Hello world!');
      
    //   // Get the image data URI
    //   $qrCodeDataUri = $qrCode->writeDataUri();
          // Créer un objet QrCode pour l'événement
//   $qrCode = new QrCode($evenement->getId());

  // Générer l'image QR code
//   $qrCodeImg = $this->get('endroid.qrcode')->get($qrCode, ['size' => 300]);
//   $qrCodeImg = QrCode->get($qrCode, ['size' => 300]);

// Set the QR code options


// Generate the QR code image data URI
// $qrCodeDataUri = (new DataUriWriter())->write($qrCode);
//   $qrCodeImg = $qrCode->get('png', ['size' => 300]);
// $qrCodeImg=$qrCodeDataUri;

// $qrCode = new QrCode('Hello world!');

// // Get the image data as a PNG string
// $qrCodeImg = $qrCode->writeDataUri();


          // Générer le contenu HTML
          $evenement=$rep->find($id);
          $evenementText = "Evenement: {$evenement->getId()} nombre max : {$evenement->getNbMax()}";
        $evenementtitre= "Titre : {$evenement->getTitreEv()}";
                  $writer = new PngWriter();
          $qrCode = QrCode::create($evenementText)
          ->setEncoding(new Encoding('UTF-8'))
          ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
          ->setSize(120)
          ->setMargin(0)
          ->setForegroundColor(new Color(0, 0, 0))
          ->setBackgroundColor(new Color(255, 255, 255));
          $logo = Logo::create('img/logo.png')
          ->setResizeToWidth(60);
      $label = Label::create('')->setFont(new NotoSans(8));
      $simple = $writer->write(
        $qrCode,
        null,
        $label->setText($evenementtitre)
    )->getDataUri();


        
          $html = $this->renderView('pdf/index.html.twig', [
              'evenement' => $evenement,
             'simple'=>$simple,
              
          ]);
 

          
          //Générer le PDF avec le contenu HTML
           
           $options = new OptionsResolver();
            $options->setDefaults([
              'defaultFont' => 'Arial',
               'fontSize' => 12,
           ]);
            
    
         $dompdf = new Dompdf($options);
    
       
       $html = $this->renderView('pdf/index.html.twig', [
              'evenement' => $evenement,
             'simple'=>$simple,
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

// Générer le PDF avec le contenu HTML

$options = new OptionsResolver();
$options->setDefaults([
    'defaultFont' => 'Arial',
    'fontSize' => 12,
]);

$dompdf = new Dompdf($options);


        


  //     { public function qrcode(Evenement $evenement)
   //{
   // $qrCode = new QrCode($evenement->getId());
  //  $qrCode->setSize(300);
   // $qrCode->setMargin(10);

   // $response = new Response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);

  //  return $response;
//}


//#[Route('/evenement/{id?}/qrcode', name: 'app_evenement_qrcode')]
//public function qrcode(Evenement $evenement, QrCode $qrCode, $id): Response
//{
  //  $qrCode->setText('http://example.com')
    //       ->setSize(300)
      //     ->setPadding(10)
        //   ->setErrorCorrection('high')
          // ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
          // ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
          // ->setLabel('My label')
         //  ->setLabelFontSize(16)
         //  ->setImageType(QrCode::IMAGE_TYPE_PNG);

    // $qrCodeImg = $qrCode->writeString();

    // return $this->render('pdf/index.html.twig', [
       // 'evenement' => $evenement,
       // 'qrCodeImg' => $qrCodeImg,
    //]);
//}

        
    
}
}


