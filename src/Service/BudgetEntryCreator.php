<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\BudgetEntry;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;

class BudgetEntryCreator
{
  /** @var Budget */
  private $budget;
  /** @var Category */
  private $category;
  /** @var BudgetEntryRepository */
  private $repository;

  public function __construct(BudgetEntryRepository $repository, Budget $budget, Category $category)
  {
    $this->budget = $budget;
    $this->category = $category;
    $this->repository = $repository;
  }

  public function findAndUpdate(?int $month, $plan, $real): BudgetEntry
  {
    $entry = $this->repository->findOneByOrNew([
      'budget' => $this->budget,
      'category' => $this->category,
      'month' => $month,
    ]);
    $entry->setBudget($this->budget);
    $entry->setCategory($this->category);
    $entry->setMonth($month);

    if($plan)
    {
      $entry->setPlan((float)$plan);
    }

    if($real)
    {
      $entry->setReal((float)$real);
    }

    return $entry;
  }
}
