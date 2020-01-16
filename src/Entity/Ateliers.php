<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AteliersRepository")
 * @UniqueEntity("nom", message="Ce nom est déjà utilisé")
 */
class Ateliers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\Length(min = 2, max=100)
     */
    private $nom;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $event;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visibility;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cover;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(max=30)
     */
    private $alt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Details", mappedBy="atelier", cascade={"remove"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $details;

    /**
     * Ateliers constructor.
     */
    public function __construct()
    {
        $this->details = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getEvent(): ?bool
    {
        return $this->event;
    }

    public function setEvent(?bool $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDetails()
    {
        return $this->details;
    }

    public function getVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(?bool $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param mixed $cover
     */
    public function setCover($cover): void
    {
        $this->cover = $cover;
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param mixed $alt
     */
    public function setAlt($alt): void
    {
        $this->alt = $alt;
    }

}
