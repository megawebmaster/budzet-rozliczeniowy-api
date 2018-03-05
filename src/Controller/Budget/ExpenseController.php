<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\BudgetEntry;
use App\Entity\BudgetExpense;
use App\Entity\Category;
use App\Repository\BudgetExpenseRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

class ExpenseController extends FOSRestController
{
  /**
   * @Route(
   *   "/budgets/{year}/expenses/{month}",
   *   name="budget_expenses",
   *   methods={"GET"},
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param Budget $budget
   * @param int $month
   * @return JsonResponse
   */
  public function index(Budget $budget, int $month)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budget' => $budget, 'month' => $month]);

    return $this->json($items, 200, [], ['groups' => ['expense']]);
  }

  /**
   * @Route(
   *   "/budgets/{year}/expenses/{month}",
   *   methods={"POST"},
   *   name="new_budget_expense",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @ParamConverter("category")
   * @param Budget $budget
   * @param Category $category
   * @param int $month
   * @param Request $request
   * @param Validator $validator
   * @return JsonResponse
   */
  public function create(Budget $budget, Category $category, int $month, Request $request, Validator $validator)
  {
    $expense = new BudgetExpense();
    $expense->setBudget($budget);
    $expense->setCategory($category);
    $expense->setMonth($month);
    $expense->setValue((float)$request->get('value'));
    $expense->setDay((int)$request->get('day'));
    $expense->setDescription($request->get('description'));

    $errors = $validator->validate($expense);
    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
    }

    $entry = $this->getMatchingEntry($budget, $month, $category);
    $entry->addReal($expense->getValue());

    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->persist($expense);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($expense, 201, [], ['groups' => ['expense']]);
  }

  /**
   * @Route(
   *   "/budgets/{year}/expenses/{month}/{id}",
   *   methods={"PUT"},
   *   name="update_budget_expense",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @ParamConverter("category", isOptional=true)
   * @param BudgetExpense $expense
   * @param Category $category
   * @param Request $request
   * @param Validator $validator
   * @return JsonResponse
   */
  public function update(BudgetExpense $expense, Category $category, Request $request, Validator $validator)
  {
    if($category)
    {
      $expense->setCategory($category);
    }

    $currentValue = $expense->getValue();
    $value = $request->get('value');
    if($value)
    {
      $expense->setValue((float)$value);
    }

    $day = $request->get('day');
    if($day)
    {
      $expense->setDay((int)$day);
    }

    $description = $request->get('description');
    if($description !== null)
    {
      $expense->setDescription($description);
    }

    $errors = $validator->validate($expense);
    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
    }

    $entry = $this->getMatchingEntry($expense->getBudget(), $expense->getMonth(), $category);
    $entry->updateReal($currentValue, $expense->getValue());

    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->persist($expense);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($expense, 200, [], ['groups' => ['expense']]);
  }

  /**
   * @Route("/budgets/{year}/expenses/{month}/{id}", methods={"DELETE"}, name="delete_budget_expense")
   * @param BudgetExpense $expense
   * @return Response
   */
  public function delete(BudgetExpense $expense)
  {
    $entry = $this->getMatchingEntry($expense->getBudget(), $expense->getMonth(), $expense->getCategory());
    $entry->subtractReal($expense->getValue());

    $this->getDoctrine()->getManager()->persist($entry);
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

  private function getMatchingEntry(Budget $budget, int $month, Category $category): BudgetEntry
  {
    $entry = $this->getDoctrine()->getRepository(BudgetEntry::class)->findOneBy([
      'budget' => $budget,
      'category' => $category,
      'month' => $month
    ]);

    if(!$entry)
    {
      $entry = new BudgetEntry();
      $entry->setBudget($budget);
      $entry->setCategory($category);
      $entry->setMonth($month);
    }

    return $entry;
  }
}
