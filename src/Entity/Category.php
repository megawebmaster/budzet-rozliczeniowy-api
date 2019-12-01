<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"name", "type", "parent"})
 */
class Category
{
  const TYPES = ['expense', 'income', 'irregular', 'saving'];

  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"category", "entry", "expense", "budget"})
   */
  private $id;

  /**
   * @var Budget Budget this category belongs to.
   * @ORM\ManyToOne(targetEntity="Budget", inversedBy="categories")
   * @Groups("category")
   */
  private $budget;

  /**
   * @var string Name of the category.
   * @ORM\Column(type="text")
   * @Assert\NotBlank(message="category.name.invalid")
   * @Groups("category")
   */
  private $name;

  /**
   * @var string Type of the category: expense, income, irregular
   * @ORM\Column(type="string")
   * @Assert\Choice(callback="getTypes", message="category.type.invalid")
   * @Groups({"category", "entry", "expense", "budget"})
   * TODO: Move it back to simple "category" group - when fixed in frontend
   */
  private $type;

  /**
   * @var Category Parent category.
   * @ORM\ManyToOne(targetEntity="Category", inversedBy="subcategories")
   * @Groups("category")
   */
  private $parent;

  /**
   * @var Category[] Subcategories list.
   * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
   */
  private $subcategories;

  /**
   * @var BudgetEntry[] Entries list.
   * @ORM\OneToMany(targetEntity="BudgetEntry", mappedBy="category")
   */
  private $entries;

  /**
   * @var BudgetExpense[] Expenses list.
   * @ORM\OneToMany(targetEntity="BudgetExpense", mappedBy="category")
   */
  private $expenses;

  /**
   * @var \DateTime Creation time.
   * @ORM\Column(type="datetime")
   * @Groups("category")
   */
  private $createdAt;

  /**
   * @var \DateTime Start time.
   * @ORM\Column(type="datetime")
   * @Groups("category")
   */
  private $startedAt;

  /**
   * @var \DateTime Deletion time.
   * @ORM\Column(type="datetime", nullable=true)
   * @Groups("category")
   */
  private $deletedAt;

  /**
   * @var string Creator's ID
   * @ORM\Column(type="string", length=50, nullable=false)
   */
  private $creatorId;

  /**
   * @var string[] Values from last 12 months of expenses.
   * @Groups("category")
   */
  private $averageValues = [];

  public static function getTypes(): array
  {
    return self::TYPES;
  }

  public function __construct()
  {
    $this->subcategories = new ArrayCollection();
    $this->entries = new ArrayCollection();
    $this->expenses = new ArrayCollection();
  }

  /**
   * @ORM\PrePersist
   */
  public function onPrePersist()
  {
    $this->createdAt = new \DateTime('now');
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getCreatorId(): ?string
  {
    return $this->creatorId;
  }

  public function setCreatorId(string $creatorId): void
  {
    $this->creatorId = $creatorId;
  }

  public function getBudget(): ?Budget
  {
    return $this->budget;
  }

  public function setBudget(Budget $budget): void
  {
    $this->budget = $budget;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(string $type): void
  {
    $this->type = $type;
  }

  public function getParent(): ?Category
  {
    return $this->parent;
  }

  public function setParent(Category $parent): void
  {
    $this->parent = $parent;
  }

  public function getCreatedAt(): \DateTime
  {
    return $this->createdAt;
  }

  public function getStartedAt(): ?\DateTime
  {
    return $this->startedAt;
  }

  public function setStartedAt(\DateTime $startedAt): void
  {
    $this->startedAt = $startedAt;
  }

  public function getDeletedAt(): ?\DateTime
  {
    return $this->deletedAt;
  }

  public function setDeletedAt(?\DateTime $deletedAt): void
  {
    $this->deletedAt = $deletedAt;
  }

  public function getAverageValues(): array
  {
    return $this->averageValues;
  }

  public function setAverageValues(array $averageValues): void
  {
    $this->averageValues = $averageValues;
  }
}
