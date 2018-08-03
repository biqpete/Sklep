<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $cpu;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ram;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $drive;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $screen;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCpu(): ?string
    {
        return $this->cpu;
    }

    public function setCpu(?string $cpu): self
    {
        $this->cpu = $cpu;

        return $this;
    }

    public function getRam(): ?int
    {
        return $this->ram;
    }

    public function setRam(?int $ram): self
    {
        $this->ram = $ram;

        return $this;
    }

    public function getDrive(): ?int
    {
        return $this->drive;
    }

    public function setDrive(?int $drive): self
    {
        $this->drive = $drive;

        return $this;
    }

    public function getScreen(): ?int
    {
        return $this->screen;
    }

    public function setScreen(?int $screen): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
