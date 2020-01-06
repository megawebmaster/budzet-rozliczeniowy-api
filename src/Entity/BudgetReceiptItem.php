<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetReceiptItemRepository")
 */
class BudgetReceiptItem
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
   * @var BudgetReceipt Receipt this item belongs to.
   * @ORM\ManyToOne(targetEntity="BudgetReceipt", inversedBy="items")
   */
  private $receipt;

  /**
   * @var Category
   * @ORM\ManyToOne(targetEntity="Category")
   * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
   * @Assert\NotBlank(message="budget_receipt_item.category.invalid")
   * @Groups({"receipt"})
   */
  private $category;

  /**
   * @var string Expense value
   * @ORM\Column(type="text")
   * @Assert\NotBlank(message="budget_receipt_item.value.invalid")
   * @Groups({"receipt"})
   */
  private $value;

  /**
   * @var string Description
   * @ORM\Column(type="text")
   * @Groups({"receipt"})
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

  public function getCategory(): ?Category
  {
    return $this->category;
  }

  public function setCategory(Category $category): void
  {
    $this->category = $category;
  }

  public function getReceipt(): ?BudgetReceipt
  {
    return $this->receipt;
  }

  public function setReceipt(BudgetReceipt $receipt): void
  {
    $this->receipt = $receipt;
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
