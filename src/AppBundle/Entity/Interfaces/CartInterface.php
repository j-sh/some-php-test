<?php declare (strict_types = 1);

namespace AppBundle\Entity\Interfaces;

use Doctrine\Common\Collections\Collection;

interface CartInterface
{
    public function addProduct(ProductInterface $product): self;

    public function removeProduct(ProductInterface $product): self;

    public function getProducts(): Collection;

    public function getSubtotal(): MoneyInterface;

    public function getVatAmount(): MoneyInterface;

    public function getTotal(): MoneyInterface;
}
