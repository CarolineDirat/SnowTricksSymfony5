<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=VideoRepository::class)
 */
class Video
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private ?string $code = null;

    /**
     * @ORM\Column(type="string", length=45)
     *
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Choice({"youtube", "vimeo", "dailymotion"})
     */
    private ?string $service = null;

    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="videos")
     */
    private ?Trick $trick;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): self
    {
        if (in_array(strtolower($service), ['youtube', 'vimeo', 'dailymotion'])) {
            $this->service = strtolower($service);

            return $this;
        }
        $this->service = null;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}
