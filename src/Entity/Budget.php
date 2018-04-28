<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
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
   * @Groups({"budget", "budget_year"})
   */
  private $id;

  /**
   * @var string
   * @ORM\Column(type="string", length=50)
   * @Assert\NotBlank
   * @Groups({"budget", "budget_year"})
   */
  private $slug;

  /**
   * @var string Name of the budget.
   * @ORM\Column(type="string", length=50)
   * @Assert\NotBlank
   * @Groups("budget")
   */
  private $name;

  /**
   * @var boolean Whether this is a default budget for the user.
   * @ORM\Column(type="boolean", options={"default": false})
   * @Assert\NotNull
   * @Groups("budget")
   */
  private $isDefault = false;

  /**
   * @var Category[] List of categories
   * @ORM\OneToMany(targetEntity="Category", mappedBy="budget")
   */
  private $categories;

  /**
   * @var string Owner's ID
   * @ORM\Column(type="string", length=50, nullable=false)
   */
  private $userId;

  public function __construct()
  {
    $this->categories = new ArrayCollection();
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

  /**
   * @return string
   */
  public function getSlug(): ?string
  {
    return $this->slug;
  }

  /**
   * @param string $slug
   */
  public function setSlug(string $slug): void
  {
    $this->slug = $slug;
  }

  /**
   * @return string
   */
  public function getUserId(): ?string
  {
    return $this->userId;
  }

  /**
   * @param string $userId
   */
  public function setUserId(string $userId): void
  {
    $this->userId = $userId;
  }

  /**
   * @return string
   */
  public function getName(): ?string
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
   * @return bool
   */
  public function isDefault(): bool
  {
    return $this->isDefault;
  }

  /**
   * @param bool $isDefault
   */
  public function setIsDefault(bool $isDefault): void
  {
    $this->isDefault = $isDefault;
  }

  /**
   * @return Category[]
   */
  public function getCategories(): Collection
  {
    return $this->categories;
  }

  /**
   * @param Category[] $categories
   */
  public function setCategories(array $categories): void
  {
    $this->categories = $categories;
  }
}
