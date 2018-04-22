<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BudgetRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, Budget::class);
  }

  public function create(int $year): Budget
  {
    $budget = new Budget();
    $budget->setName('budget');
    $budget->setYear($year);

    try
    {
      $this->getEntityManager()->persist($budget);
      $this->getEntityManager()->flush();

      return $budget;
    } catch (ORMException $e) {
      return null;
    }
  }

  public function getAvailableYears(): ArrayCollection
  {
    $elements = $this
      ->createQueryBuilder('o')
      ->select('o.year')
      ->getQuery()
      ->execute()
    ;
    $collection = new ArrayCollection($elements);

    return $collection->map(function($item){ return $item['year']; });
  }
}
