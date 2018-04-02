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

  public function findAll()
  {
    $averages = $this->getAverageExpenses();

    /** @var Category[] $results */
    $results = parent::findBy([], ['id' => 'ASC']);

    foreach($results as $result)
    {
      if (isset($averages[$result->getId()]))
      {
        $result->setAverageValue((float)$averages[$result->getId()]['average']);
      }
    }

    return $results;
  }

  /**
   * @return array
   */
  private function getAverageExpenses(): array
  {
// TODO: Check for proper users support
    $averagesQuery = <<<SQL
SELECT e.category_id, AVG(e.real) AS average FROM (
  SELECT d.category_id, d.real, 
    row_number() OVER (PARTITION BY d.category_id ORDER BY db.year DESC, d.month DESC) AS rank
  FROM budget_entry d
  LEFT JOIN budget db ON db.id = d.budget_id
  WHERE d.real > 0
) AS e
WHERE e.rank <= 12
GROUP BY e.category_id
SQL;
    $mapping = new ResultSetMapping();
    $mapping->addScalarResult('average', 'average');
    $mapping->addIndexByScalar('category_id');

    $averages = $this->getEntityManager()
      ->createNativeQuery($averagesQuery, $mapping)
      ->getResult(AbstractQuery::HYDRATE_ARRAY);

    return $averages;
  }
}
