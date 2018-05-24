<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetExpenseRepository")
 */
class BudgetExpense
{
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"expense", "budget"})
   */
  private $id;

  /**
   * @var BudgetYear Budget year this expense belongs to.
   * @ORM\ManyToOne(targetEntity="BudgetYear", inversedBy="expenses")
   * @Groups("expense")
   */
  private $budgetYear;

  /**
   * @var Category
   * @ORM\ManyToOne(targetEntity="Category")
   * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
   * @Assert\NotBlank(message="budget_expense.category.invalid")
   * @Groups({"expense", "budget"})
   */
  private $category;

  /**
   * @var integer Month this expense was made.
   * @ORM\Column(type="integer")
   * @Assert\Range(
   *   min="1",
   *   max="12",
   *   minMessage="budget_expense.month.invalid",
   *   maxMessage="budget_expense.month.invalid"
   * )
   * @Groups({"expense", "budget"})
   */
  private $month;

  /**
   * @var integer Day this expense was made.
   * @ORM\Column(type="integer", nullable=true)
   * @Assert\Range(
   *   min="1",
   *   max="31",
   *   minMessage="budget_expense.day.invalid",
   *   maxMessage="budget_expense.day.invalid"
   * )
   * @Groups({"expense", "budget"})
   */
  private $day;

  /**
   * @var string Expense value
   * @ORM\Column(type="text")
   * @Assert\NotBlank(message="budget_expense.value.invalid")
   * @Groups({"expense", "budget"})
   */
  private $value;

  /**
   * @var string Description
   * @ORM\Column(type="text")
   * @Groups({"expense", "budget"})
   */
  private $description;

  /**
   * @var string Creator's ID
   * @ORM\Column(type="string", length=50, nullable=false)
   */
  private $creatorId;

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

  public function getBudgetYear(): ?BudgetYear
  {
    return $this->budgetYear;
  }

  public function setBudgetYear(BudgetYear $budgetYear): void
  {
    $this->budgetYear = $budgetYear;
  }

  public function getCategory(): ?Category
  {
    return $this->category;
  }

  public function setCategory(Category $category): void
  {
    $this->category = $category;
  }

  public function getMonth(): ?int
  {
    return $this->month;
  }

  public function setMonth(int $month): void
  {
    $this->month = $month;
  }

  public function getDay(): ?int
  {
    return $this->day;
  }

  public function setDay(?int $day): void
  {
    $this->day = $day;
  }

  public function getValue(): ?string
  {
    return $this->value;
  }

  public function setValue(string $value): void
  {
    $this->value = $value;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }
}
