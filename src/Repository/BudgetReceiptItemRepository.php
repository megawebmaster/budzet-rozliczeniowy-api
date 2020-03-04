<?php

namespace App\Repository;

use App\Entity\BudgetReceiptItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BudgetReceiptItemRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, BudgetReceiptItem::class);
  }
}
