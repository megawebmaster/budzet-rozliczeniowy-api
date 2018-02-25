<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"name", "type"})
 */
class Category
{
  const TYPES = ['expense', 'income', 'irregular'];

  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"category", "entry", "expense", "budget"})
   */
  private $id;

  /**
   * @var string Name of the category.
   * @ORM\Column(type="string", length=50)
   * @Assert\NotBlank()
   * @Groups("category")
   */
  private $name;

  /**
   * @var string Type of the category: expense, income, irregular
   * @ORM\Column(type="string")
   * @Assert\Choice(callback="getTypes")
   * @Groups("category")
   */
  private $type;

  /**
   * @var Category Parent category.
   * @ORM\ManyToOne(targetEntity="Category", inversedBy="subcategories")
   * @Groups("category")
   */
  private $parent;

  /**
   * @var Category[] Subcategories list.
   * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
   */
  private $subcategories;

  /**
   * @var \DateTime Creation time.
   * @ORM\Column(type="datetime")
   * @Groups("category")
   */
  private $createdAt;

  /**
   * @var \DateTime Deletion time.
   * @ORM\Column(type="datetime", nullable=true)
   * @Groups("category")
   */
  private $deletedAt;

  public static function getTypes(): array
  {
    return self::TYPES;
  }

  public function __construct()
  {
    $this->subcategories = new ArrayCollection();
  }

  /**
   * Triggered on insert
   *
   * @ORM\PrePersist
   */
  public function onPrePersist()
  {
    $this->createdAt = new \DateTime("now");
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
  public function setId(int $id): void
  {
    $this->id = $id;
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
   * @return string
   */
  public function getType(): string
  {
    return $this->type;
  }

  /**
   * @param string $type
   */
  public function setType(string $type): void
  {
    $this->type = $type;
  }

  /**
   * @return Category
   */
  public function getParent(): ?Category
  {
    return $this->parent;
  }

  /**
   * @param Category $parent
   */
  public function setParent(Category $parent): void
  {
    $this->parent = $parent;
  }

  /**
   * @return \DateTime
   */
  public function getCreatedAt(): \DateTime
  {
    return $this->createdAt;
  }

  /**
   * @return \DateTime
   */
  public function getDeletedAt(): ?\DateTime
  {
    return $this->deletedAt;
  }

  /**
   * @param \DateTime $deletedAt
   */
  public function setDeletedAt(\DateTime $deletedAt): void
  {
    $this->deletedAt = $deletedAt;
  }

  public function isDeleted(): bool
  {
    return $this->deletedAt != null;
  }
}
