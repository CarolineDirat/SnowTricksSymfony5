<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

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
    private ?UuidInterface $uuid;

    /**
     * @ORM\Column(type="string", length=200, unique=true)
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
     * @ORM\Column(name="created_at", type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime_immutable")
     */
    private DateTimeImmutable $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Picture::class, mappedBy="trick", orphanRemoval=true, cascade={"persist","remove"})
     */
    private ?Collection $pictures;

    /**
     * @ORM\OneToMany(targetEntity=Video::class, mappedBy="trick", orphanRemoval=true, cascade={"persist","remove"})
     */
    private ?Collection $videos;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="trick", orphanRemoval=true, cascade={"persist","remove"})
     */
    private ?Collection $comments;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="tricks")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Group $groupTrick;

    /**
     * @ORM\OneToOne(targetEntity=Picture::class, cascade={"persist", "remove"})
     * @ORM\joinColumn(onDelete="SET NULL")
     */
    private ?Picture $firstPicture = null;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): self
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
        return strtolower($this->slug);
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

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setTrick($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            // set the owning side to null (unless already changed)
            if ($picture->getTrick() === $this) {
                $picture->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

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

    public function getFirstPicture(): ?Picture
    {
        return $this->firstPicture;
    }

    public function setFirstPicture(?Picture $firstPicture): self
    {
        // $firstPicture must be one of $this->pictures or null
        if (empty($firstPicture)) {
            $this->firstPicture = null;

            return $this;
        }
        if ($this->hasPicture($firstPicture)) {
            $this->firstPicture = $firstPicture;

            return $this;
        }
        $this->firstPicture = null;

        return $this;
    }

    public function hasPicture(Picture $pictureToTest): bool
    {
        foreach ($this->pictures as $picture) {
            if ($picture->getFileName() === $pictureToTest->getFilename()) {
                return true; // filename is unique in picture table
            }
        }
        return false;
    }
}
