<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetAccessRepository")
 */
class BudgetAccess
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   * @Groups("budget")
   */
  private $id;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Budget", inversedBy="accesses")
   * @ORM\JoinColumn(nullable=false)
   */
  private $budget;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   * @Assert\NotBlank(message="budget.owner.invalid")
   * @Groups("budget")
   */
  private $userId;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   * @Assert\NotBlank(message="budget.name.blank")
   * @Groups("budget")
   */
  private $name;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   * @Groups("budget")
   */
  private $slug;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   * @Assert\NotNull(message="budget.default.invalid")
   * @Groups("budget")
   */
  private $isDefault = false;

  /**
   * @ORM\Column(type="text", nullable=true)
   * @Groups("budget")
   */
  private $abilities;

  public function getId()
  {
    return $this->id;
  }

  public function getBudget(): ?Budget
  {
    return $this->budget;
  }

  public function setBudget(?Budget $budget): self
  {
    $this->budget = $budget;

    return $this;
  }

  public function getUserId(): ?string
  {
    return $this->userId;
  }

  public function setUserId(?string $userId): self
  {
    $this->userId = $userId;

    return $this;
  }

  public function getRecipient(): ?string
  {
    return $this->recipient;
  }

  public function setRecipient(?string $recipient): self
  {
    $this->recipient = $recipient;

    return $this;
  }

  public function getAbilities()
  {
    return $this->abilities;
  }

  public function setAbilities($abilities): self
  {
    $this->abilities = $abilities;

    return $this;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getSlug(): ?string
  {
    return $this->slug;
  }

  public function setSlug(?string $slug): self
  {
    $this->slug = $slug;

    return $this;
  }

  public function getIsDefault(): ?bool
  {
    return $this->isDefault;
  }

  public function setIsDefault(bool $isDefault): self
  {
    $this->isDefault = $isDefault;

    return $this;
  }
}
