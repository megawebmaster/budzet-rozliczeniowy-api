<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Category::class);
  }

  public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
  {
    /** @var Category[] $results */
    $results = parent::findBy($criteria, ['id' => 'ASC']);

    if (isset($criteria['budget'])) {
      /** @var int $budget */
      $budget   = $criteria['budget']->getId();
      $averages = $this->getAverageExpenses($budget);

      foreach ($results as $result) {
        if (isset($averages[$result->getId()])) {
          $result->setAverageValues($averages[$result->getId()]);
        }
      }
    }

    return $results;
  }

  public function findLeafCategoryIds()
  {
    return $this->createQueryBuilder('c')
                ->select('c.id')
                ->where('c.id NOT IN (:parents)')
                ->setParameter('parents', $this->findNonEmptyParentCategoryIds())
                ->getQuery()
                ->setHydrationMode(Query::HYDRATE_SCALAR)
                ->execute();
  }

  private function findNonEmptyParentCategoryIds()
  {
    return $this->createQueryBuilder('c')
                ->select('p.id')
                ->leftJoin('c.parent', 'p')
                ->where('p.id IS NOT NULL')
                ->getQuery()
                ->setHydrationMode(Query::HYDRATE_SCALAR)
                ->execute();
  }

  /**
   * @param int $budgetId
   *
   * @return array
   */
  private function getAverageExpenses(int $budgetId): array
  {
    try{
      $now  = new \DateTime();
      $date = $now->sub(new \DateInterval('P1Y'))->format('Y-m').'-01';

      $averagesQuery = <<<SQL
SELECT d.category_id, d.real_value, CONCAT(db.year,'-',d.month,'-01') AS entry_id
FROM budget_entry d
LEFT JOIN budget_year db ON db.id = d.budget_year_id
WHERE db.budget_id = '$budgetId' AND STR_TO_DATE(CONCAT(db.year,'-',d.month,'-01'), '%Y-%m-%d') > '$date'
SQL;

      $mapping = new ResultSetMapping();
      $mapping->addScalarResult('real_value', 'real');
      $mapping->addScalarResult('category_id', 'category_id');
      $mapping->addScalarResult('entry_id', 'entry_id');

      $results = $this->getEntityManager()
                      ->createNativeQuery($averagesQuery, $mapping)
                      ->getResult(AbstractQuery::HYDRATE_ARRAY);

      $averages     = [];
      $firstEntries = [];
      foreach ($results as $result) {
        if ( ! isset($averages[$result['category_id']])) {
          $averages[$result['category_id']] = [];
        }
        if ( ! isset($firstEntries[$result['category_id']]) || $firstEntries[$result['category_id']] > $result['entry_id']) {
          $firstEntries[$result['category_id']] = $result['entry_id'];
        }

        $averages[$result['category_id']][] = $result['real'];
      }

      // Fill in missing entries to properly calculate values
      foreach ($firstEntries as $categoryId => $entryId) {
        $difference = $now->diff(new \DateTime($entryId));
        if (count($averages[$categoryId]) < $difference->m) {
          $averages[$categoryId] += array_fill(0, $difference->m - count($averages[$categoryId]), '');
        }
      }

      return $averages;
    } catch (\Exception $e){
      return [];
    }
  }
}
