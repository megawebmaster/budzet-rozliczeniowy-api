<?php

namespace App\Repository;

use App\Entity\BudgetExpense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BudgetExpenseRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, BudgetExpense::class);
  }
}
