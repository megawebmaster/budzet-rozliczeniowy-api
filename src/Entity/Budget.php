<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetRepository")
 */
class Budget
{
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"budget", "entry", "expense"})
   */
  private $id;

  /**
   * @var integer Year the budget is for.
   * @ORM\Column(type="integer")
   * @Assert\NotBlank
   * @Groups("budget")
   */
  private $year;

  /**
   * @var string Name of the budget.
   * @ORM\Column(type="string", length=50)
   * @Assert\NotBlank
   * @Groups("budget")
   */
  private $name;

  /**
   * @var BudgetEntry[] List of entries
   * @ORM\OneToMany(targetEntity="BudgetEntry", mappedBy="budget")
   * @Groups("budget")
   */
  private $entries;

  /**
   * @var BudgetExpense[] List of expenses
   * @ORM\OneToMany(targetEntity="BudgetExpense", mappedBy="budget")
   * @Groups("budget")
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
  public function getId(): int
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

  /**
   * @return int
   */
  public function getYear(): int
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
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName(string $name): void
  {
    $this->name = $name;
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
