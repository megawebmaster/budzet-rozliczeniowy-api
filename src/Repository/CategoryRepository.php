<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CategoryRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, Category::class);
  }

  public function findOneByOrNew(array $criteria, array $orderBy = null, $limit = null, $offset = null): Category
  {
    $category = $this->findOneBy($criteria, $orderBy, $limit, $offset);

    if(!$category)
    {
      $category = new Category();
    }

    return $category;
  }
}
