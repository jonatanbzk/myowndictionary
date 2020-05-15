<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @UniqueEntity(
 *     fields={"user", "language_1", "language_2"}
 * )
 */
class Tag
{
    const LANGUAGES = [
        0 => 'English',
        1 => 'French',
        2 => 'German',
        3 => 'Polish',
        4 => 'Russian',
        5 => 'Italian',
        6 => 'Portuguese',
        7 => 'Spanish',
        8 => 'Esperanto'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tags")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Assert\Regex("/^[0-8]$/")
     * @ORM\Column(type="smallint")
     */
    private $language_1;

    /**
     * @Assert\Regex("/^[0-8]$/")
     * @ORM\Column(type="smallint")
     */
    private $language_2;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Term", mappedBy="tag", orphanRemoval=true)
     */
    private $terms;

    public function __construct()
    {
        $this->terms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLanguage1(): ?int
    {
        return $this->language_1;
    }

    public function setLanguage1(int $language_1): self
    {
        $this->language_1 = $language_1;

        return $this;
    }

    public function getLanguage2(): ?int
    {
        return $this->language_2;
    }

    public function setLanguage2(int $language_2): self
    {
        $this->language_2 = $language_2;

        return $this;
    }

    public function getLangStr(): array
    {
         return [
             self::LANGUAGES[$this->language_1],
             self::LANGUAGES[$this->language_2]];
    }

    /**
     * @return Collection|Term[]
     */
    public function getTerms(): Collection
    {
        return $this->terms;
    }

    public function addTerm(Term $term): self
    {
        if (!$this->terms->contains($term)) {
            $this->terms[] = $term;
            $term->setTag($this);
        }

        return $this;
    }

    public function removeTerm(Term $term): self
    {
        if ($this->terms->contains($term)) {
            $this->terms->removeElement($term);
            // set the owning side to null (unless already changed)
            if ($term->getTag() === $this) {
                $term->setTag(null);
            }
        }

        return $this;
    }
}