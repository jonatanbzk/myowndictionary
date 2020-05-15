<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TermRepository")
 * @UniqueEntity(
 *     fields={"tag", "word", "translation"},
 * )
 */
class Term
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="terms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tag;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $word;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $translation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $add_at;

    public function __construct()
    {
        $this->add_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(string $translation): self
    {
        $this->translation = $translation;

        return $this;
    }

    public function getAddAt(): ?\DateTimeInterface
    {
        return $this->add_at;
    }

    public function setAddAt(\DateTimeInterface $add_at): self
    {
        $this->add_at = $add_at;

        return $this;
    }
}
