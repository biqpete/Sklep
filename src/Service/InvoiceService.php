<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 18/09/2018
 * Time: 12:22
 */

namespace App\Service;

use Knp\Snappy\Pdf;

/**
 * Class InvoiceService
 * @package App\Service
 */
class InvoiceService
{
    /**
     * @var Pdf
     */
    private $pdf;

    /**
     * InvoiceService constructor.
     * @param Pdf $pdf
     */
    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @param $html
     * @param $userName
     * @param $orderName
     */
    public function pdfAction($html, $userName, $orderName){
            $this->pdf->generateFromHtml($html, 'invoices/'.$userName.$orderName.'invoice.pdf');
    }
}
