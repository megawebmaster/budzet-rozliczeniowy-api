<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetYearRepository")
 */
class BudgetYear
{
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"budget_year", "entry", "expense"})
   */
  private $id;

  /**
   * @var Budget Budget this year belongs to.
   * @ORM\ManyToOne(targetEntity="Budget", inversedBy="entries")
   * @Groups("entry")
   */
  private $budget;

  /**
   * @var integer Year the budget is for.
   * @ORM\Column(type="integer")
   * @Assert\NotBlank(message="budget_year.year.invalid")
   * @Assert\GreaterThan(value="0", message="budget_year.year.positive")
   * @Groups("budget_year")
   */
  private $year;

  /**
   * @var BudgetEntry[] List of entries
   * @ORM\OneToMany(targetEntity="BudgetEntry", mappedBy="budgetYear")
   * @Groups("budget_year")
   */
  private $entries;

  /**
   * @var BudgetExpense[] List of expenses
   * @ORM\OneToMany(targetEntity="BudgetExpense", mappedBy="budgetYear")
   * @Groups("budget_year")
   */
  private $expenses;

  public function __construct()
  {
    $this->entries = new ArrayCollection();
    $this->expenses = new ArrayCollection();
  }

  /**
   * @return int
   */
  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId($id): void
  {
    $this->id = $id;
  }

  public function getBudget(): ?Budget
  {
    return $this->budget;
  }

  public function setBudget(Budget $budget): void
  {
    $this->budget = $budget;
  }

  /**
   * @return int
   */
  public function getYear(): ?int
  {
    return $this->year;
  }

  /**
   * @param int $year
   */
  public function setYear(int $year): void
  {
    $this->year = $year;
  }

  /**
   * @return Collection<BudgetEntry>
   */
  public function getEntries(): Collection
  {
    return $this->entries;
  }

  /**
   * @param BudgetEntry[] $entries
   */
  public function setEntries(array $entries): void
  {
    $this->entries = $entries;
  }

  /**
   * @return Collection<BudgetExpense>
   */
  public function getExpenses(): Collection
  {
    return $this->expenses;
  }

  /**
   * @param BudgetExpense[] $expenses
   */
  public function setExpenses(array $expenses): void
  {
    $this->expenses = $expenses;
  }
}
