<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AccountRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, Account::class);
  }

  public function findForYear(int $year)
  {
    return $this->getForYearQuery($year)->getQuery()->execute();
  }

  public function findOneOrNew(int $year, string $name)
  {
    $account = $this->getForYearQuery($year)
      ->andWhere('o.name = :name')
      ->setParameter('name', $name)
      ->getQuery()
      ->execute();

    if(!$account)
    {
      $account = new Account();
    }

    return $account;
  }

  private function getForYearQuery(int $year)
  {
    return $this->createQueryBuilder('o')
      ->where('o.startedAt >= :start_date AND (o.deletedAt IS NULL OR o.deletedAt <= :end_date)')
      ->setParameter('start_date', $year.'-01-01')
      ->setParameter('end_date', $year.'-12-31');
  }
}
