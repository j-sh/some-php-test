<?php declare (strict_types = 1);

namespace AppBundle\Entity\Interfaces;

interface MoneyInterface
{
    public function setCents(int $cents): self;

    public function getCents(): int;

    public function setEuros(int $euros): self;

    public function getEuros(): int;
}
