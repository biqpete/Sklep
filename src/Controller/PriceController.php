<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 04/08/2018
 * Time: 17:59
 */

namespace App\Controller;

class PriceController
{
    private $price;

    /**
     * @return double
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param double $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    public function calculate($cpu=null,$ram=null,$drive=null,$screen=null)
    {
        $price = 0;

        switch($cpu){
            case null:
                break;
            case 'i3':
                $price += 200;
                break;
            case 'i5':
                $price += 400;
                break;
            case 'i7':
                $price += 600;
                break;
        }

        switch($ram){
            case null:
               // break;
            case 8:
                $price += 200;
               // break;
            case 16:
                $price += 400;
                //break;
            case 32:
                $price += 600;
                break;
        }

        switch($drive){
            case null:
                break;
            case 128:
                $price += 200;
                break;
            case 256:
                $price += 400;
                break;
            case 512:
                $price += 600;
                break;
        }

        switch($screen){
            case null:
                break;
            case 10:
                $price += 200;
                break;
            case 13:
                $price += 400;
                break;
            case 15:
                $price += 600;
                break;
        }

        return $price;
    }
}