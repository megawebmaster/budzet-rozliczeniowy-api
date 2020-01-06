<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\BudgetEntry;
use App\Entity\BudgetYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BudgetEntryRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
    parent::__construct($registry, BudgetEntry::class);
  }

  public function findOneByOrNew(array $criteria, array $orderBy = null): BudgetEntry
  {
    $entry = $this->findOneBy($criteria, $orderBy);

    if ( ! $entry) {
      $entry = new BudgetEntry();
    }

    return $entry;
  }

  public function getIrregularEntries(BudgetYear $budgetYear)
  {
    /** @var BudgetEntry[] $results */
    $results       = $this->findBy(['budgetYear' => $budgetYear, 'month' => null]);
    $monthlyValues = $this->getMonthlyEntries(
      $budgetYear,
      array_map(
        function ($entry) {
          /** @var BudgetEntry $entry */
          return $entry->getCategory()->getId();
        },
        $results
      )
    );

    foreach ($results as $result) {
      if (isset($monthlyValues[$result->getCategory()->getId()])) {
        $result->setMonthlyRealValues($monthlyValues[$result->getCategory()->getId()]);
      }
    }

    return $results;
  }


  private function getMonthlyEntries(BudgetYear $budgetYear, array $ids): array
  {
    try{
      $id           = $budgetYear->getId();
      $irregularIds = join(',', $ids);

      $averagesQuery = <<<SQL
SELECT d.category_id, d.real_value
FROM budget_entry d
LEFT JOIN budget_year db ON db.id = d.budget_year_id
WHERE d.budget_year_id = '$id' 
  AND d.real_value != ''
  AND d.category_id IN ($irregularIds)
SQL;

      $mapping = new ResultSetMapping();
      $mapping->addScalarResult('real_value', 'real');
      $mapping->addScalarResult('category_id', 'category_id');

      $results = $this->getEntityManager()
                      ->createNativeQuery($averagesQuery, $mapping)
                      ->getResult(AbstractQuery::HYDRATE_ARRAY);

      $values = [];
      foreach ($results as $result) {
        if ( ! isset($values[$result['category_id']])) {
          $values[$result['category_id']] = [];
        }

        $values[$result['category_id']][] = $result['real'];
      }

      return $values;
    } catch (\Exception $e){
      return [];
    }
  }
}
