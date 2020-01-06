<?php
declare(strict_types=1);

namespace App;

use App\Entity\BudgetYear;
use App\Entity\Category;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class BudgetExpense
{
  /**
   * @var integer
   * @Groups({"expense", "budget"})
   */
  private $id;

  /**
   * @var BudgetYear Budget year this expense belongs to.
   * @Groups("expense")
   */
  private $budgetYear;

  /**
   * @var Category
   * @Assert\NotBlank(message="budget_expense.category.invalid")
   * @Groups({"expense", "budget"})
   */
  private $category;

  /**
   * @var integer Month this expense was made.
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
   * @Assert\NotBlank(message="budget_expense.value.invalid")
   * @Groups({"expense", "budget"})
   */
  private $value;

  /**
   * @var string Description
   * @Groups({"expense", "budget"})
   */
  private $description;

  /**
   * @var string Creator's ID
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
