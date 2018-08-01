<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\BudgetAccess;
use App\Security\User\Auth0User;
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

  public function findForUser(Auth0User $user, $criteria = [])
  {
    $builder = $this->createQueryBuilder('b')
      ->innerJoin(BudgetAccess::class, 'bs')
      ->where('bs.userId = :userId')
      ->setParameter('userId', $user->getId())
    ;

    $idx = 0;
    foreach($criteria as $key => $value)
    {
      $builder->andWhere("$key = ?$idx")->setParameter($idx, $value);
      $idx += 1;
    }

    return $builder->getQuery()->getSingleResult();
  }
}
