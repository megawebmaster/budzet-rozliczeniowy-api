<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\BudgetExpense;
use App\Entity\Category;
use App\Repository\BudgetExpenseRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
   * @Route(
   *   "/budgets/{year}/expenses",
   *   methods={"POST"},
   *   name="new_budget_expense",
   *   requirements={"year": "\d{4}"}
   * )
   * @ParamConverter("category")
   * @param Budget $budget
   * @param Category $category
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function create(Budget $budget, Category $category, Request $request, ValidatorInterface $validator)
  {
    $expense = new BudgetExpense();
    $expense->setBudget($budget);
    $expense->setCategory($category);
    $expense->setMonth((int)$request->get('month'));
    $expense->setValue((float)$request->get('value'));
    $expense->setDay((int)$request->get('day'));
    $expense->setDescription($request->get('description'));
    $errors = $validator->validate($expense);

    if(count($errors) > 0)
    {
      return $this->json($errors);
    }

    $this->getDoctrine()->getManager()->persist($expense);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($expense, 201, [], ['groups' => ['expense']]);
  }

  /**
   * @Route(
   *   "/budgets/{year}/expenses/{id}",
   *   methods={"PUT"},
   *   name="update_budget_expense",
   *   requirements={"year": "\d{4}"}
   * )
   * @ParamConverter("category", isOptional=true)
   * @param BudgetExpense $expense
   * @param Category $category
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function update(BudgetExpense $expense, Category $category, Request $request, ValidatorInterface $validator)
  {
    if($category)
    {
      $expense->setCategory($category);
    }
    if($request->request->has('value'))
    {
      $expense->setValue((float)$request->get('value'));
    }
    if($request->request->has('day'))
    {
      $expense->setDay((int)$request->get('day'));
    }
    if($request->request->has('description'))
    {
      $expense->setDescription($request->get('description'));
    }
    $errors = $validator->validate($expense);

    if(count($errors) > 0)
    {
      return $this->json($errors);
    }

    $this->getDoctrine()->getManager()->persist($expense);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($expense, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route("/budgets/{year}/expenses/{id}", methods={"DELETE"}, name="delete_budget_expense")
   * @param BudgetExpense $expense
   * @return Response
   */
  public function delete(BudgetExpense $expense)
  {
    $this->getDoctrine()->getManager()->remove($expense);
    $this->getDoctrine()->getManager()->flush();

    return new Response();
  }

  /**
   * @return BudgetExpenseRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetExpense::class);
  }
}
