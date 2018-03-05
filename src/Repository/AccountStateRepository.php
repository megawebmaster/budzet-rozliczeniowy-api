<?php

namespace App\Repository;

use App\Entity\AccountState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AccountStateRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, AccountState::class);
  }

  public function findOneByOrNew(array $criteria, array $orderBy = null): AccountState
  {
    $state = $this->findOneBy($criteria, $orderBy);

    if(!$state)
    {
      $state = new AccountState();
    }

    return $state;
  }
}
