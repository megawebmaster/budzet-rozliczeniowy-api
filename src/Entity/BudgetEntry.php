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
   * @var string Planned value
   * @ORM\Column(type="text")
   * @Assert\NotNull
   * @Groups({"entry", "budget"})
   */
  private $plan = '';

  /**
   * @var string Real value
   * @ORM\Column(type="text")
   * @Assert\NotNull
   * @Groups({"entry", "budget"})
   */
  private $real = '';

  /**
   * @var string Creator's ID
   * @ORM\Column(type="string", length=50, nullable=false)
   * @Assert\NotBlank()
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

  /**
   * @return string
   */
  public function getCreatorId(): ?string
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

  public function setMonth(?int $month): void
  {
    $this->month = $month;
  }

  public function getPlan(): ?string
  {
    return $this->plan;
  }

  public function setPlan(string $plan): void
  {
    $this->plan = $plan;
  }

  public function getReal(): ?string
  {
    return $this->real;
  }

  public function setReal(string $real): void
  {
    $this->real = $real;
  }
}
