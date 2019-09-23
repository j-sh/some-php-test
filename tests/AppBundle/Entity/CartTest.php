<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Money;
use AppBundle\Entity\Product;
use PHPUnit_Framework_TestCase;

class CartTest extends PHPUnit_Framework_TestCase
{
    public function testCartProducts()
    {

        $cart = new Cart();
        $this->assertEquals(0, $cart->getProducts()->count());


        $price1 = new Money();
        $price1->setEuros(15);
        $price1->setCents(65);

        $product1 = new Product();
        $product1->setPrice($price1);
        $product1->setVatRate(0.2);

        $cart->addProduct($product1);

        // Check if cart totals are correct
        $this->assertEquals(15, $cart->getSubtotal()->getEuros());
        $this->assertEquals(65, $cart->getSubtotal()->getCents());

        $this->assertEquals(3, $cart->getVatAmount()->getEuros());
        $this->assertEquals(13, $cart->getVatAmount()->getCents());

        $this->assertEquals(18, $cart->getTotal()->getEuros());
        $this->assertEquals(78, $cart->getTotal()->getCents());


        $price2 = new Money();
        $price2->setEuros(2);
        $price2->setCents(40);

        $product2 = new Product();
        $product2->setPrice($price2);
        $product2->setVatRate(0.25);
        $cart->addProduct($product2);

        // Check if cart totals are correct
        $this->assertEquals(18, $cart->getSubtotal()->getEuros());
        $this->assertEquals(5, $cart->getSubtotal()->getCents());

        $this->assertEquals(3, $cart->getVatAmount()->getEuros());
        $this->assertEquals(73, $cart->getVatAmount()->getCents());

        $this->assertEquals(21, $cart->getTotal()->getEuros());
        $this->assertEquals(78, $cart->getTotal()->getCents());
    }
}
