<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
// Declares Category as an API Platform resource
#[ApiResource(
    normalizationContext: ['groups' => ['category:read']],      // Fields visible in API responses
    denormalizationContext: ['groups' => ['category:write']]    // Fields writable when receiving data
)]
class Category
{
    // Auto-generated ID, visible in category and book read contexts
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read', 'book:read'])]
    private ?int $id = null;

    // Category name must be unique, readable and writable
    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['category:read', 'category:write', 'book:read', 'book:write'])]
    private ?string $name = null;

    /**
     * Many-to-Many relationship:
     * - A category can contain multiple books
     * - A book can belong to multiple categories
     * This is the inverse side of the relation (mappedBy="categories" in Book)
     *
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'categories')]
    private Collection $books;

    public function __construct()
    {
        // Initialize the collection to prevent null issues
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

    /**
     * Returns all books associated with this category.
     *
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    /**
     * Adds a book to this category and ensures the relation
     * stays synchronized on the Book side.
     */
    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->addCategory($this); // Keep the relation consistent
        }

        return $this;
    }

    /**
     * Removes a book from this category and ensures the relation
     * is removed on the Book side as well.
     */
    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            $book->removeCategory($this); // Also remove category from the book
        }

        return $this;
    }
}
