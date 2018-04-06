<?php


namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension{
    public function getFilters() {
        return array(
            new TwigFilter('price', array($this, 'priceFilter')),
            new TwigFilter('array_from_method', array($this, 'arrayFromMethodFilter')),
        );
    }

    public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',') {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$'.$price;

        return $price;
    }

    public function arrayFromMethodFilter($elements, $f) {
        $res = [];
        foreach ($elements as $e) {
            $res[] = $e->{$f}();
        }
        return $res;
    }
}