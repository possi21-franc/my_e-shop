<?php

namespace App\Controller;

use App\Repository\OderRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BillController extends AbstractController
{
    #[Route('/editor/order/{id}/bill', name: 'app_bill')]
    public function index($id, OderRepository $oderRepository): Response
    {

        $order = $oderRepository->find($id);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('bill/index.html.twig', [
            'order' => $order,]);
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream('bill-'.$order->getId().'.pdf', [
                'Attachment'=> false
            ]);

        return new Response('',200, [
            'Content-Type'=> 'application/pdf',
        ]);
    }
}
