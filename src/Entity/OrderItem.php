<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending';

    /**
     * @var Collection<int, OrderItemStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: OrderItemStatusHistory::class, mappedBy: 'orderItem', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $statusHistories;

    public function __construct()
    {
        $this->statusHistories = new ArrayCollection();
    }


    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderRef = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderRef(): ?Order
    {
        return $this->orderRef;
    }

    public function setOrderRef(?Order $orderRef): static
    {
        $this->orderRef = $orderRef;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection<int, OrderItemStatusHistory>
     */
    public function getStatusHistories(): Collection
    {
        return $this->statusHistories;
    }

    public function addStatusHistory(OrderItemStatusHistory $statusHistory): static
    {
        if (!$this->statusHistories->contains($statusHistory)) {
            $this->statusHistories->add($statusHistory);
            $statusHistory->setOrderItem($this);
        }

        return $this;
    }

    public function removeStatusHistory(OrderItemStatusHistory $statusHistory): static
    {
        if ($this->statusHistories->removeElement($statusHistory)) {
            // set the owning side to null (unless already changed)
            if ($statusHistory->getOrderItem() === $this) {
                $statusHistory->setOrderItem(null);
            }
        }

        return $this;
    }
}
