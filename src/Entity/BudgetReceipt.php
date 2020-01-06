<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetReceiptRepository")
 */
class BudgetReceipt
{
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"receipt"})
   */
  private $id;

  /**
   * @var BudgetYear Budget year this receipt belongs to.
   * @ORM\ManyToOne(targetEntity="BudgetYear", inversedBy="receipts")
   * @Groups("receipt")
   */
  private $budgetYear;

  /**
   * @var integer Month this receipt was made.
   * @ORM\Column(type="integer")
   * @Assert\Range(
   *   min="1",
   *   max="12",
   *   minMessage="budget_receipt.month.invalid",
   *   maxMessage="budget_receipt.month.invalid"
   * )
   * @Groups({"receipt"})
   */
  private $month;

  /**
   * @var integer Day this receipt was made.
   * @ORM\Column(type="integer", nullable=true)
   * @Assert\Range(
   *   min="1",
   *   max="31",
   *   minMessage="budget_receipt.day.invalid",
   *   maxMessage="budget_receipt.day.invalid"
   * )
   * @Groups({"receipt"})
   */
  private $day;

  /**
   * @var string Shop in which the receipt was made.
   * @ORM\Column(type="string", nullable=true)
   * @Groups({"receipt"})
   */
  private $shop;

  /**
   * @var BudgetReceiptItem[] List of items
   * @ORM\OneToMany(targetEntity="BudgetReceiptItem", mappedBy="receipt", fetch="EAGER")
   * @Groups("receipt")
   */
  private $items;

  /**
   * @var string Creator's ID
   * @ORM\Column(type="string", length=50, nullable=false)
   */
  private $creatorId;

  public function __construct()
  {
    $this->items = new ArrayCollection();
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

  public function getBudgetYear(): ?BudgetYear
  {
    return $this->budgetYear;
  }

  public function setBudgetYear(BudgetYear $budgetYear): void
  {
    $this->budgetYear = $budgetYear;
  }

  /**
   * @return Collection<BudgetReceiptItem>
   */
  public function getItems(): Collection
  {
    return $this->items;
  }

  /**
   * @param BudgetReceiptItem[] $items
   */
  public function setItems(array $items): void
  {
    $this->items = $items;
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

  public function getShop(): ?string
  {
    return $this->shop;
  }

  public function setShop(?string $shop): void
  {
    $this->shop = $shop;
  }
}
