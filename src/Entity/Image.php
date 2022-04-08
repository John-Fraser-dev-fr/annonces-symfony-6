<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\File(
        maxSize: "2000k",
        mimeTypes: "application/jpeg, application/jpg",
        mimeTypesMessage : "Veuillez télécharger votre image aux formats JPG ou JPEG !",
        maxSizeMessage: "Votre image ne doit pas dépasser 2 Mo !")]
    private $image;

    #[ORM\ManyToOne(targetEntity: Annonce::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private $annonce;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getAnnonce(): ?Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(?Annonce $annonce): self
    {
        $this->annonce = $annonce;

        return $this;
    }
}
