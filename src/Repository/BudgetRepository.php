<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\BudgetAccess;
use App\Security\User\Auth0User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BudgetRepository extends ServiceEntityRepository
{
  /** @var TokenStorageInterface */
  private $tokenStorage;

  public function __construct(TokenStorageInterface $tokenStorage, RegistryInterface $registry)
  {
    parent::__construct($registry, Budget::class);
    $this->tokenStorage = $tokenStorage;
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

  /**
   * @param Request $request
   * @return Budget|null
   */
  public function getFromRequest(Request $request): ?Budget
  {
    $token = $this->tokenStorage->getToken();
    if($token === null)
    {
      return null;
    }

    /** @var Auth0User $user */
    $user = $token->getUser();
    $criteria = ['userId' => $user->getId()];

    if(($id = $request->get('budget_id')) !== null)
    {
      $criteria['budget_id'] = $id;
    }
    else if(($slug = $request->get('budget_slug')) !== null)
    {
      $criteria['slug'] = $slug;
    }
    else
    {
      return null;
    }

    $budgetAccessRepository = $this->getEntityManager()->getRepository(BudgetAccess::class);
    /** @var Budget $budget */
    $budget = $budgetAccessRepository->findOneBy($criteria)->getBudget();

    return $budget;
  }
}
