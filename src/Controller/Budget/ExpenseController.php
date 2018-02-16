<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\BudgetExpense;
use App\Repository\BudgetExpenseRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExpenseController extends Controller
{
  /**
   * @Route("/budgets/{year}/expenses", name="budget_expenses", methods={"GET"}, requirements={"year": "\d{4}"})
   * @param Budget $budget
   * @return JsonResponse
   */
  public function index(Budget $budget)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budget' => $budget]);
    return $this->json($items, 200, [], ['groups' => ['expense']]);
  }

  /**
   * @return BudgetExpenseRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetExpense::class);
  }
}
