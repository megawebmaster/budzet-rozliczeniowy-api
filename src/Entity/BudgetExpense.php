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
   * @var Budget Budget this expense belongs to.
   * @ORM\ManyToOne(targetEntity="Budget", inversedBy="expenses")
   * @Groups("expense")
   */
  private $budget;

  /**
   * @var Category
   * @ORM\ManyToOne(targetEntity="Category")
   * @Assert\NotBlank
   * @Groups({"expense", "budget"})
   */
  private $category;

  /**
   * @var integer Month this expense was made.
   * @ORM\Column(type="integer")
   * @Assert\Range(min="1", max="12")
   * @Groups({"expense", "budget"})
   */
  private $month;

  /**
   * @var integer Day this expense was made.
   * @ORM\Column(type="integer")
   * @Assert\Range(min="1", max="31")
   * @Groups({"expense", "budget"})
   */
  private $day;

  /**
   * @var double Expense value
   * @ORM\Column(type="decimal", precision=8, scale=2)
   * @Assert\NotBlank
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
   * @return int
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId(int $id): void
  {
    $this->id = $id;
  }

  /**
   * @return Budget
   */
  public function getBudget(): Budget
  {
    return $this->budget;
  }

  /**
   * @param Budget $budget
   */
  public function setBudget(Budget $budget): void
  {
    $this->budget = $budget;
  }

  /**
   * @return Category
   */
  public function getCategory(): Category
  {
    return $this->category;
  }

  /**
   * @param Category $category
   */
  public function setCategory(Category $category): void
  {
    $this->category = $category;
  }

  /**
   * @return int
   */
  public function getMonth(): int
  {
    return $this->month;
  }

  /**
   * @param int $month
   */
  public function setMonth(int $month): void
  {
    $this->month = $month;
  }

  /**
   * @return int
   */
  public function getDay(): int
  {
    return $this->day;
  }

  /**
   * @param int $day
   */
  public function setDay(int $day): void
  {
    $this->day = $day;
  }

  /**
   * @return float
   */
  public function getValue(): float
  {
    return (float)$this->value;
  }

  /**
   * @param float $value
   */
  public function setValue(float $value): void
  {
    $this->value = $value;
  }

  /**
   * @return string
   */
  public function getDescription(): string
  {
    return $this->description;
  }

  /**
   * @param string $description
   */
  public function setDescription(string $description): void
  {
    $this->description = $description;
  }
}
