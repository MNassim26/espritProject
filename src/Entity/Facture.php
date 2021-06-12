<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FactureRepository::class)
 */
class Facture
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\OneToOne(targetEntity=order::class, inversedBy="facture", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $confirmedOrder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getConfirmedOrder(): ?order
    {
        return $this->confirmedOrder;
    }

    public function setConfirmedOrder(order $confirmedOrder): self
    {
        $this->confirmedOrder = $confirmedOrder;

        return $this;
    }
}
