<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AuthorRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Author
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Book::class, inversedBy="authors")
     */
    private $books;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length( 
     *      min = 2,
     *      max = 70,
     *      minMessage = "First name must be at least {{ limit }} characters long",
     *      maxMessage = "First name cannot be longer than {{ limit }} characters");
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $booksAmount;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Book[]
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        $this->books->removeElement($book);

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

    public function getBooksAmount(): ?int
    {
        return $this->booksAmount;
    }

    public function setBooksAmount(?int $booksAmount): self
    {
        $this->booksAmount = $booksAmount;

        return $this;
    }
}
