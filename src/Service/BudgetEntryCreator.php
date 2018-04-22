<?php

namespace App\Service;

use App\Entity\BudgetEntry;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;
use App\Security\User\Auth0User;

class BudgetEntryCreator
{
  /** @var BudgetYear */
  private $budgetYear;
  /** @var Category */
  private $category;
  /** @var BudgetEntryRepository */
  private $repository;
  /** @var Auth0User */
  private $user;

  public function __construct(
    BudgetEntryRepository $repository,
    BudgetYear $budgetYear,
    Category $category,
    Auth0User $user
  )
  {
    $this->category = $category;
    $this->budgetYear = $budgetYear;
    $this->repository = $repository;
    $this->user = $user;
  }

  public function findAndUpdate(?int $month, $plan, $real): BudgetEntry
  {
    $entry = $this->repository->findOneByOrNew([
      'budgetYear' => $this->budgetYear,
      'category' => $this->category,
      'month' => $month,
    ]);
    $entry->setBudgetYear($this->budgetYear);
    $entry->setCategory($this->category);
    $entry->setMonth($month);

    if($entry->getId() === null)
    {
      $entry->setCreatorId($this->user->getId());
    }

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
