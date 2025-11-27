<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\BookRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
// Declares this class as an API Platform resource
#[ApiResource(
    normalizationContext: ['groups' => ['book:read']],      // Fields visible in API responses
    denormalizationContext: ['groups' => ['book:write']]    // Fields allowed when sending data
)]
// Enables filtering books by publication date (exact, before, after, etc.)
#[ApiFilter(DateFilter::class, properties: ['publicationDate'])]
class Book
{
    // Auto-incremented book ID, visible in read mode
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['book:read'])]
    private ?int $id = null;

    // Book title, readable and writable
    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $title = null;

    // Description of the book, optional
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $description = null;

    // Publication date, optional
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['book:read', 'book:write'])]
    private ?DateTime $publicationDate = null;

    // Many books belong to one author
    // inversedBy='books' → matches Author::$books
    #[ORM\ManyToOne(inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: false)] // Book must always have an author
    #[Groups(['book:read', 'book:write'])]
    private ?Author $author = null;

    /**
     * Many-to-Many relationship:
     * - A book can belong to several categories
     * - A category can contain several books
     *
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'books')]
    #[Groups(['book:read', 'book:write'])]
    private Collection $categories;

    public function __construct()
    {
        // Always initialize Doctrine collections in the constructor
        $this->categories = new ArrayCollection();
    }

    // ---------- GETTERS & SETTERS ----------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPublicationDate(): ?DateTime
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?DateTime $publicationDate): static
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     *                                   Returns all categories linked to this book
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * Adds a category to the book if it’s not already linked.
     */
    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * Removes a category from the book.
     */
    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
