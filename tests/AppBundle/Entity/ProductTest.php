<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Money;
use AppBundle\Entity\Product;
use PHPUnit_Framework_TestCase;

class ProductTest extends PHPUnit_Framework_TestCase
{
    public function testProduct()
    {
        // Check if new product contains correct values
        $product = new Product();
        $product->setName('testName1');

        $price = new Money();
        $price->setEuros(14);
        $price->setCents(56);

        $product->setPrice($price);
        $product->setAvailable(5);
        $product->setVatRate(0.5);

        $this->assertEquals('testName1', $product->getName());
        $this->assertEquals(5, $product->getAvailable());
        $this->assertEquals(0.5, $product->getVatRate());
        $this->assertEquals(14, $product->getPrice()->getEuros());
        $this->assertEquals(56, $product->getPrice()->getCents());
        $this->assertEquals(2184, $product->getTotalInCents());
        $this->assertEquals(728, $product->getVatInCents());
    }
}
