<?php

namespace AppBundle\Entity\Interfaces;

use Doctrine\Common\Collections\ArrayCollection;

interface StockInterface
{
    public function addProduct(ProductInterface $product): self;

    public function removeProduct(ProductInterface $product): self;

    public function getProducts(): ArrayCollection;
}