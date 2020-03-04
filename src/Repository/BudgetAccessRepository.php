<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\BudgetAccess;
use App\Security\User\Auth0User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BudgetAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method BudgetAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method BudgetAccess[]    findAll()
 * @method BudgetAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetAccessRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, BudgetAccess::class);
  }

  public function findOneByOrNew(Auth0User $user, array $criteria, array $orderBy = null): BudgetAccess
  {
    $criteria['userId'] = $user->getId();
    $access             = $this->findOneBy($criteria, $orderBy);

    if ( ! $access) {
      $access = new BudgetAccess();
      $budget = new Budget();
      $budget->setUserId($user->getId());
      $budget->addAccess($access);
      $this->getEntityManager()->persist($budget);
      $this->getEntityManager()->persist($access);
    }

    return $access;
  }
}
