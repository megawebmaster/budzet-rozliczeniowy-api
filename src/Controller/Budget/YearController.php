<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\BudgetYear;
use App\Repository\BudgetYearRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class YearController extends Controller
{
  /**
   * @Route("/budgets/{budget_slug}", name="budget_years", methods={"GET"})
   * @param Budget $budget
   * @return JsonResponse
   */
  public function index(Budget $budget)
  {
    return $this->json($this->getRepository()->getAvailableYears($budget));
  }

  /**
   * @Route("/budgets/{budget_slug}/{year}", name="budget_year", methods={"GET"}, requirements={"year": "\d{4}"})
   * @param Budget $budget
   * @param int $year
   * @return JsonResponse
   */
  public function show(Budget $budget, int $year)
  {
    $repository = $this->getRepository();
    $budgetYear = $repository->findOneBy(['year' => $year, 'budget' => $budget]);

    if(!$budgetYear)
    {
      $budgetYear = new BudgetYear();
      $budgetYear->setYear($year);
      $budgetYear->setBudget($budget);
      $this->getDoctrine()->getManager()->persist($budgetYear);
      $this->getDoctrine()->getManager()->flush();
    }

    return $this->json($budgetYear, 200, [], ['groups' => ['budget_year']]);
  }

  /**
   * @return BudgetYearRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetYear::class);
  }
}
