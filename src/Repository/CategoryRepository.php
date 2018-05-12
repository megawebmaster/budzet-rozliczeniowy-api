<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CategoryRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, Category::class);
  }

  public function findOneByOrNew(array $criteria, array $orderBy = null): Category
  {
    $category = $this->findOneBy($criteria, $orderBy);

    if(!$category)
    {
      $category = new Category();
    }

    return $category;
  }

  public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
  {
    /** @var int $budget */
    $budget = $criteria['budget']->getId();
    $averages = $this->getAverageExpenses($budget);

    /** @var Category[] $results */
    $results = parent::findBy($criteria, ['id' => 'ASC']);

    foreach($results as $result)
    {
      if (isset($averages[$result->getId()]))
      {
        $result->setAverageValues($averages[$result->getId()]);
      }
    }

    return $results;
  }

  /**
   * @param int $budgetId
   * @return array
   */
  private function getAverageExpenses(int $budgetId): array
  {
    try
    {
      $now = new \DateTime();
      $date = $now->sub(new \DateInterval('P1Y'))->format('Y-m').'-01';

      $averagesQuery = <<<SQL
SELECT d.category_id, d.real_value
FROM budget_entry d
LEFT JOIN budget_year db ON db.id = d.budget_year_id
WHERE db.budget_id = '$budgetId' 
  AND d.real_value != '' 
  AND STR_TO_DATE(CONCAT(db.year,'-',d.month,'-01'), '%Y-%m-%d') > '$date'
SQL;

      $mapping = new ResultSetMapping();
      $mapping->addScalarResult('real_value', 'real');
      $mapping->addScalarResult('category_id', 'category_id');

      $results = $this->getEntityManager()
        ->createNativeQuery($averagesQuery, $mapping)
        ->getResult(AbstractQuery::HYDRATE_ARRAY);

      $averages = [];
      foreach($results as $result)
      {
        if(!isset($averages[$result['category_id']]))
        {
          $averages[$result['category_id']] = [];
        }

        $averages[$result['category_id']][] = $result['real'];
      }

      return $averages;
    } catch(\Exception $e) {
      return [];
    }
  }
}
