<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\BudgetYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class BudgetYearRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, BudgetYear::class);
  }

  public function getAvailableYears(Budget $budget): ArrayCollection
  {
    $elements = $this
      ->createQueryBuilder('o')
      ->select('o.year')
      ->where('o.budget = :budget')
      ->setParameters(['budget' => $budget])
      ->getQuery()
      ->execute();
    $collection = new ArrayCollection($elements);

    return $collection->map(
      function ($item) {
        return $item['year'];
      }
    );
  }
}
