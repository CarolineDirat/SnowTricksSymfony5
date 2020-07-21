<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Group;

/**
 * @ORM\Entity(repositoryClass=TrickRepository::class)
 */
class Trick
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="uuid_binary")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $slug;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private ?DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private ?DateTimeImmutable $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="tricks")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Group $groupTrick;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getGroupTrick(): ?Group
    {
        return $this->groupTrick;
    }

    public function setGroupTrick(?Group $groupTrick): self
    {
        $this->groupTrick = $groupTrick;

        return $this;
    }
}
