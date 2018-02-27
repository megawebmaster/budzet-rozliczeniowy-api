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
   * @var Budget Budget this entry belongs to.
   * @ORM\ManyToOne(targetEntity="Budget", inversedBy="entries")
   * @Groups("entry")
   */
  private $budget;

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

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getBudget(): Budget
  {
    return $this->budget;
  }

  public function setBudget(Budget $budget): void
  {
    $this->budget = $budget;
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
}
