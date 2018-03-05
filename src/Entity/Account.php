<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Account
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"account", "account_state"})
   */
  private $id;

  /**
   * @var string Account name.
   * @ORM\Column(type="string", length=50)
   * @Assert\NotBlank()
   * @Groups("account")
   */
  private $name;

  /**
   * @var string Account description.
   * @ORM\Column(type="string", length=255, nullable=true)
   * @Groups("account")
   */
  private $description;

  /**
   * @var \DateTime Creation time.
   * @ORM\Column(type="datetime")
   * @Groups("account")
   */
  private $createdAt;

  /**
   * @var \DateTime Start time.
   * @ORM\Column(type="datetime")
   * @Groups("category")
   */
  private $startedAt;

  /**
   * @var \DateTime Deletion time.
   * @ORM\Column(type="datetime", nullable=true)
   * @Groups("category")
   */
  private $deletedAt;

  /**
   * @var AccountState[] List of state changes
   * @ORM\OneToMany(targetEntity="AccountState", mappedBy="account")
   * @Groups("account")
   */
  private $states;

  public function __construct()
  {
    $this->states = new ArrayCollection();
  }

  /**
   * @ORM\PrePersist
   */
  public function onPrePersist()
  {
    $this->createdAt = new \DateTime('now');
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id): void
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
   * @return string|null
   */
  public function getDescription(): ?string
  {
    return $this->description;
  }

  /**
   * @param string|null $description
   */
  public function setDescription(?string $description): void
  {
    $this->description = $description;
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
  public function getStartedAt(): \DateTime
  {
    return $this->startedAt;
  }

  /**
   * @param \DateTime $startedAt
   */
  public function setStartedAt(\DateTime $startedAt): void
  {
    $this->startedAt = $startedAt;
  }

  /**
   * @return \DateTime
   */
  public function getDeletedAt(): ?\DateTime
  {
    return $this->deletedAt;
  }

  /**
   * @param \DateTime|null $deletedAt
   */
  public function setDeletedAt(?\DateTime $deletedAt): void
  {
    $this->deletedAt = $deletedAt;
  }

  /**
   * @return Collection<AccountState>
   */
  public function getStates(): Collection
  {
    return $this->states;
  }

  /**
   * @param AccountState[] $states
   */
  public function setStates(array $states): void
  {
    $this->states = $states;
  }
}
