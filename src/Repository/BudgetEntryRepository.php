<?php

namespace App\Repository;

use App\Entity\BudgetEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BudgetEntryRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, BudgetEntry::class);
  }

  public function findOneByOrNew(array $criteria, array $orderBy = null): BudgetEntry
  {
    $entry = $this->findOneBy($criteria, $orderBy);

    if(!$entry)
    {
      $entry = new BudgetEntry();
      $entry->setPlan(0.0);
      $entry->setReal(0.0);
    }

    return $entry;
  }
}
