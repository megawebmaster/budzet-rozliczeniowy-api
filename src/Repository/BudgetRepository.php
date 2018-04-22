<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BudgetRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, Budget::class);
  }

  public function findOneByOrNew(array $criteria, array $orderBy = null): Budget
  {
    $budget = $this->findOneBy($criteria, $orderBy);

    if(!$budget)
    {
      $budget = new Budget();
    }

    return $budget;
  }
}
