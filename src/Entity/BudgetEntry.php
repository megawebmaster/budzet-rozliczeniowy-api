<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetEntryRepository")
 */
class BudgetEntry
{
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"entry", "budget"})
   */
  private $id;

  /**
   * @var BudgetYear Budget year this entry belongs to.
   * @ORM\ManyToOne(targetEntity="BudgetYear", inversedBy="entries")
   * @Groups("entry")
   */
  private $budgetYear;

  /**
   * @var Category Category ID this entry belongs to.
   * @ORM\ManyToOne(targetEntity="Category")
   * @Assert\NotBlank
   * @Groups({"entry", "budget"})
   */
  private $category;

  /**
   * @var integer Month this entry belongs to.
   * @ORM\Column(type="integer", nullable=true)
   * @Assert\Range(min="1", max="12")
   * @Groups({"entry", "budget"})
   */
  private $month;

  /**
   * @var float Planned value
   * @ORM\Column(type="decimal", precision=8, scale=2)
   * @Assert\NotBlank
   * @Groups({"entry", "budget"})
   */
  private $plan;

  /**
   * @var float Real value
   * @ORM\Column(type="decimal", precision=8, scale=2)
   * @Assert\NotBlank
   * @Groups({"entry", "budget"})
   */
  private $real;

  /**
   * @var string Creator's ID
   * @ORM\Column(type="string", length=50, nullable=false)
   */
  private $creatorId;

  /**
   * BudgetEntry constructor.
   */
  public function __construct()
  {
    $this->plan = 0.0;
    $this->real = 0.0;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getCreatorId(): string
  {
    return $this->creatorId;
  }

  /**
   * @param string $creatorId
   */
  public function setCreatorId(string $creatorId): void
  {
    $this->creatorId = $creatorId;
  }

  public function getBudgetYear(): BudgetYear
  {
    return $this->budgetYear;
  }

  public function setBudgetYear(BudgetYear $budgetYear): void
  {
    $this->budgetYear = $budgetYear;
  }

  public function getCategory(): Category
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

  public function setMonth(?int $month): void
  {
    $this->month = $month;
  }

  public function getPlan(): float
  {
    return (float)$this->plan;
  }

  public function setPlan(float $plan): void
  {
    $this->plan = $plan;
  }

  public function getReal(): float
  {
    return (float)$this->real;
  }

  public function setReal(float $real): void
  {
    $this->real = $real;
  }

  public function addReal(float $value): void
  {
    $this->real += $value;
  }

  public function updateReal(float $old, float $new): void
  {
    $this->real += $new - $old;
  }

  public function subtractReal(float $value): void
  {
    $this->real -= $value;
  }
}
