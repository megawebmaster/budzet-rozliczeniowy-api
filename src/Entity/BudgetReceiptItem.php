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
   * @Groups({"receipt", "receipt_item"})
   */
  private $id;

  /**
   * @var BudgetReceipt Receipt this item belongs to.
   * @ORM\ManyToOne(targetEntity="BudgetReceipt", inversedBy="items", fetch="LAZY")
   */
  private $receipt;

  /**
   * @var Category
   * @ORM\ManyToOne(targetEntity="Category")
   * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
   * @Assert\NotBlank(message="budget_receipt_item.category.invalid")
   */
  private $category;

  /**
   * @var string Expense value
   * @ORM\Column(type="text")
   * @Assert\NotBlank(message="budget_receipt_item.value.invalid")
   * @Groups({"receipt", "receipt_item"})
   */
  private $value;

  /**
   * @var string Description
   * @ORM\Column(type="text")
   * @Groups({"receipt", "receipt_item"})
   */
  private $description;

  /**
   * @var string Creator's ID
   * @ORM\Column(type="string", length=50, nullable=false)
   */
  private $creatorId;

  /**
   * @var boolean Whether migrated to WebCrypto
   * @ORM\Column(type="boolean", nullable=false)
   * @Groups({"receipt", "receipt_item"})
   */
  private $webCrypto = false;

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

  /**
   * @Groups({"receipt"})
   * @return int
   * @noinspection PhpUnused
   */
  public function getCategoryId(): int
  {
    return $this->category->getId();
  }

  public function getReceipt(): ?BudgetReceipt
  {
    return $this->receipt;
  }

  public function setReceipt(BudgetReceipt $receipt): void
  {
    $this->receipt = $receipt;
  }

  /**
   * @Groups({"receipt"})
   */
  public function getReceiptId(): int
  {
    return $this->receipt->getId();
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

  /**
   * @return bool
   */
  public function isWebCrypto(): bool
  {
    return $this->webCrypto;
  }

  /**
   * @param bool $webCrypto
   */
  public function setWebCrypto(bool $webCrypto): void
  {
    $this->webCrypto = $webCrypto;
  }
}
