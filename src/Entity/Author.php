<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
// Declares this class as an API Platform resource
#[ApiResource(
    normalizationContext: ['groups' => ['author:read']],      // What is visible when reading
    denormalizationContext: ['groups' => ['author:write']]    // What is writable when sending data
)]
// Adds a search filter on the "name" field (partial match)
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
class Author
{
    // Auto-incremented ID, exposed in read mode
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['author:read','book:read'])]
    private ?int $id = null;

    // Author name, readable and writable, also exposed inside Book
    #[ORM\Column(length: 255)]
    #[Groups(['author:read','author:write','book:read','book:write'])]
    private ?string $name = null;

    // Birth date, nullable, readable/writable
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['author:read','author:write'])]
    private ?\DateTime $birthDate = null;

    // Author biography, optional
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['author:read','author:write'])]
    private ?string $biography = null;

    /**
     * OneToMany relation:
     * - One Author can have multiple Books
     * - Book::author is the owning side of the relation
     * - Books are only exposed in read mode
     *
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'author')]
    #[Groups(['author:read'])]
    private Collection $books;

    public function __construct()
    {
        // Always initialize Doctrine collections in the constructor
        $this->books = new ArrayCollection();
    }

    // ---------- GETTERS & SETTERS ----------

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

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;
        return $this;
    }

    /**
     * Returns the list of books associated with the author
     *
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    /**
     * Adds a book to the collection if it is not already present.
     * Also updates the owning side: $book->setAuthor($this)
     */
    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->setAuthor($this); // Keep relation synchronized
        }

        return $this;
    }

    /**
     * Removes a book from the collection and unsets the author on the book
     * if the relation is still pointing to this Author.
     */
    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // Check that the book still references this author
            if ($book->getAuthor() === $this) {
                $book->setAuthor(null); // Break the relation
            }
        }

        return $this;
    }
}
