<?php


namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="shop_order")
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

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", nullable=true)
     */
    private $orderName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSent = 0;

    /**
     * @return string
     */
    public function getOrderName()
    {
        return $this->orderName;
    }

    /**
     * @param string $orderName
     */
    public function setOrderName($orderName): void
    {
        $this->orderName = $orderName;
    }

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsSent(): ?bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): self
    {
        $this->isSent = $isSent;

        return $this;
    }
}
