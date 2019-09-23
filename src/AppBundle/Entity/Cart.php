<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Interfaces\CartInterface;
use AppBundle\Entity\Interfaces\MoneyInterface;
use AppBundle\Entity\Interfaces\ProductInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Cart
 * @ORM\Table(name="cart")
 * @ORM\Entity()
 * @ORM\ChangeTrackingPolicy(value="DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Cart implements CartInterface
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    protected $id;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CartProducts", mappedBy="cart", cascade={"all"})
     * @JMS\Expose()
     */
    private $cartProducts;

    /**
     * Cart constructor.
     */
    public function __construct()
    {
        $this->cartProducts = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Add product to cart
     * @param ProductInterface $product
     * @return CartInterface
     */
    public function addProduct(ProductInterface $product): CartInterface
    {
        $cartProduct = new CartProducts();
        $cartProduct->setProduct($product);
        $cartProduct->setCart($this);
        $this->cartProducts->add($cartProduct);
        return $this;
    }

    /**
     * Remove product from cart
     * @param ProductInterface $product
     * @return CartInterface
     */
    public function removeProduct(ProductInterface $product): CartInterface
    {
        $cartProduct = new CartProducts();
        $cartProduct->setProduct($product);
        $cartProduct->setCart($this);
        $this->cartProducts->removeElement($cartProduct);
        return $this;
    }

    /**
     * Get all cartProducts
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return $this->cartProducts;
    }


    /**
     * Get all Product instances in cart
     * @return array
     */
    public function getAllProducts()
    {
        $products = [];
        foreach ($this->cartProducts as $product) {
            $products[] = $product->getProduct();
        }

        return $products;
    }

    /**
     * Calculate and return cart total
     * @return MoneyInterface
     */
    public function getTotal(): MoneyInterface
    {
        $cents = 0;
        //@TODO make sure rounding rules are followed correctly - consult/find person that knows applicable rules
        foreach ($this->getProducts() as $cartProduct) {
            $cents += $cartProduct->getProduct()->getTotalInCents() * $cartProduct->getCount();
        }

        $total = new Money();
        $total->setCents($cents);

        return $total;

    }

    /**
     * Sum VAT of all product and return sum
     * @return MoneyInterface
     */
    public function getVatAmount(): MoneyInterface
    {
        $vat = 0;
        //@TODO make sure rounding rules are followed correctly - consult/find person that knows applicable rules
        foreach ($this->getProducts() as $cartProduct) {
            $vat += $cartProduct->getProduct()->getVatInCents() * $cartProduct->getCount();
        }

        $money = new Money();
        $money->setCents($vat);

        return $money;
    }

    /**
     * Sum all product prices without TAX and sum
     * @return MoneyInterface
     */
    public function getSubtotal(): MoneyInterface
    {
        $cents = 0;
        //@TODO make sure rounding rules are followed correctly - consult/find person that knows applicable rules
        foreach ($this->getProducts() as $cartProduct) {
            /** @var MoneyInterface $price */
            $price = $cartProduct->getProduct()->getPrice();
            $cents += ($price->getEuros()*100 + $price->getCents()) * $cartProduct->getCount();
        }
        $total = new Money();
        $total->setCents($cents);
        return $total;
    }
}
