<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Interfaces\MoneyInterface;
use AppBundle\Entity\Interfaces\ProductInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StockRepository")
 * @ORM\ChangeTrackingPolicy(value="DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Product implements ProductInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     * @Assert\Length(max=100, min=3)
     * @JMS\Expose()
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="available", type="integer")
     * @Assert\GreaterThanOrEqual(0)
     * @JMS\Expose()
     */
    private $available = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="vat_rate", type="decimal", precision=4, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     * @JMS\Expose()
     */
    private $vatRate = 0;

    /**
     * @var MoneyInterface
     * @JMS\Type("AppBundle\Entity\Money")
     *
     * @ORM\Embedded(class = "AppBundle\Entity\Money")
     * @Assert\Valid()
     * @Assert\NotBlank()
     * @JMS\Expose()
     */
    private $price;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CartProducts", mappedBy="product", cascade={"all"})
     */
    private $cartProducts;

    /**
     * Product constructor.
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
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $name
     * @return ProductInterface
     */
    public function setName(string $name): ProductInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param int $available
     * @return ProductInterface
     */
    public function setAvailable(int $available): ProductInterface
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return int
     */
    public function getAvailable(): int
    {
        return $this->available;
    }

    /**
     * @param MoneyInterface $price
     * @return ProductInterface
     */
    public function setPrice(MoneyInterface $price): ProductInterface
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return MoneyInterface
     */
    public function getPrice(): MoneyInterface
    {
        return $this->price;
    }

    /**
     * @param float $vat
     * @return ProductInterface
     */
    public function setVatRate(float $vat): ProductInterface
    {
        $this->vatRate = $vat;

        return $this;
    }

    /**
     * @return float
     */
    public function getVatRate(): float
    {
        return $this->vatRate;
    }

    /**
     * Get price with VAT in cents
     *
     * @return int
     */
    public function getTotalInCents(): int
    {
        $total = $this->price->getTotalCents();
        //@TODO make sure rounding rules are followed correctly - consult/find person that knows applicable rules
        $vat  = round($total * $this->getVatRate());

        return $total + $vat;
    }

    /**
     * Get VAT amount in cents
     *
     * @return int
     */
    public function getVatInCents(): int
    {
        //@TODO make sure rounding rules are followed correctly - consult/find person that knows applicable rules
        return round($this->price->getTotalCents() * $this->getVatRate());
    }
    /**
     * @param CartProducts $cartProduct
     * @return $this
     */
    public function addCartProduct(CartProducts $cartProduct)
    {
        $this->cartProducts->add($cartProduct);

        return $this;
    }
    /**
     * @param CartProducts $cartProduct
     * @return $this
     */
    public function removeCartProduct(CartProducts $cartProduct)
    {
        $this->cartProducts->removeElement($cartProduct);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCartProducts(): Collection
    {
        return $this->cartProducts;
    }
}
