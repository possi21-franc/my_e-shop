<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $shippingCost = null;

    /**
     * @var Collection<int, Oder>
     */
    #[ORM\OneToMany(targetEntity: Oder::class, mappedBy: 'city')]
    private Collection $oders;

    public function __construct()
    {
        $this->oders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getShippingCost(): ?float
    {
        return $this->shippingCost;
    }

    public function setShippingCost(float $shippingCost): static
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    /**
     * @return Collection<int, Oder>
     */
    public function getOders(): Collection
    {
        return $this->oders;
    }

    public function addOder(Oder $oder): static
    {
        if (!$this->oders->contains($oder)) {
            $this->oders->add($oder);
            $oder->setCity($this);
        }

        return $this;
    }

    public function removeOder(Oder $oder): static
    {
        if ($this->oders->removeElement($oder)) {
            // set the owning side to null (unless already changed)
            if ($oder->getCity() === $this) {
                $oder->setCity(null);
            }
        }

        return $this;
    }
}
