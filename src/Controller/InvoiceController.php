<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 18/09/2018
 * Time: 09:26
 */

namespace App\Controller;


use App\Entity\Order;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InvoiceController extends Controller
{
    public function pdfAction(Order $order){
//        $this->get('knp_snappy.pdf')->generateFromHtml(
//            $this->renderView(
//                'invoice.html.twig',
//                array(
//                    'order'  => $order
//                )
//            ),
//            '/public/uploads_dir/file.pdf'
//        );


        $html = $this->renderView('invoice.html.twig', array(
            'order'  => $order
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'file.pdf'
        );
    }
}