<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Money;
use PHPUnit_Framework_TestCase;

class MoneyTest extends PHPUnit_Framework_TestCase
{
    public function testMoney()
    {
        // Check if new Money instance contains 0 euro, 0 cents
        $money = new Money();
        $this->assertEquals(0, $money->getEuros());
        $this->assertEquals(0, $money->getCents());
        $this->assertEquals(0, $money->getTotalCents());

        $money->setEuros(17);
        $money->setCents(54);

        // Check if money instance contains correct values for euros and cents
        $this->assertEquals(17, $money->getEuros());
        $this->assertEquals(54, $money->getCents());
        $this->assertEquals(1754, $money->getTotalCents());
    }
}
