<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountStateRepository")
 */
class AccountState
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @Groups({"account_state", "account"})
   */
  private $id;

  /**
   * @var Account Account this state belongs to.
   * @ORM\ManyToOne(targetEntity="Account", inversedBy="states")
   * @Groups("account_state")
   */
  private $account;

  /**
   * @var integer Month this state belongs to.
   * @ORM\Column(type="integer")
   * @Assert\Range(min="1", max="12")
   * @Groups({"account_state", "account"})
   */
  private $month;

  /**
   * @var float State value
   * @ORM\Column(type="decimal", precision=8, scale=2)
   * @Assert\NotBlank
   * @Groups({"account_state", "account"})
   */
  private $value;

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
   * @return Account
   */
  public function getAccount(): Account
  {
    return $this->account;
  }

  /**
   * @param Account $account
   */
  public function setAccount(Account $account): void
  {
    $this->account = $account;
  }

  /**
   * @return int
   */
  public function getMonth(): int
  {
    return (int)$this->month;
  }

  /**
   * @param int $month
   */
  public function setMonth(int $month): void
  {
    $this->month = $month;
  }

  /**
   * @return float
   */
  public function getValue(): float
  {
    return (float)$this->value;
  }

  /**
   * @param float $value
   */
  public function setValue(float $value): void
  {
    $this->value = $value;
  }
}
