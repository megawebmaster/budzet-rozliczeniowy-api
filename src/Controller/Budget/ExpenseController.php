<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetEntry;
use App\Entity\BudgetExpense;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Repository\BudgetExpenseRepository;
use App\Security\User\Auth0User;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExpenseController extends FOSRestController
{
  use ErrorRenderTrait;

  /** @var ValidatorInterface */
  private $validator;

  /**
   * @param ValidatorInterface $validator
   */
  public function __construct(ValidatorInterface $validator)
  {
    $this->validator = $validator;
  }

  /**
   * @Route(
   *   "/budgets/{budget_slug}/{year}/expenses/{month}",
   *   name="budget_expenses",
   *   methods={"GET"},
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetYear $budgetYear
   * @param int $month
   * @return JsonResponse
   */
  public function index(BudgetYear $budgetYear, int $month)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budgetYear' => $budgetYear, 'month' => $month]);

    return $this->json($items, 200, [], ['groups' => ['expense']]);
  }

  /**
   * @Route(
   *   "/budgets/{budget_slug}/{year}/expenses/{month}",
   *   methods={"POST"},
   *   name="new_budget_expense",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @ParamConverter("category")
   * @param BudgetYear $budgetYear
   * @param Category $category
   * @param int $month
   * @param Request $request
   * @return JsonResponse
   */
  public function create(BudgetYear $budgetYear, Category $category, int $month, Request $request)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $expense = new BudgetExpense();
    $expense->setBudgetYear($budgetYear);
    $expense->setCategory($category);
    $expense->setMonth($month);
    $expense->setValue($request->get('value', ''));
    $expense->setDay((int)$request->get('day'));
    $expense->setDescription($request->get('description', ''));
    $expense->setCreatorId($user->getId());

    $errors = $this->validator->validate($expense);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $entry = $this->getMatchingEntry($budgetYear, $month, $category);
    $entry->setReal($request->get('budget_value', ''));

    $errors = $this->validator->validate($entry);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors, 'budget_');
    }

    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->persist($expense);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($expense, 201, [], ['groups' => ['expense']]);
  }

  /**
   * @Route(
   *   "/budgets/{budget_slug}/{year}/expenses/{month}/{id}",
   *   methods={"PUT"},
   *   name="update_budget_expense",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @ParamConverter("category", isOptional=true)
   * @param BudgetExpense $expense
   * @param Category $category
   * @param Request $request
   * @return JsonResponse
   */
  public function update(BudgetExpense $expense, Category $category, Request $request)
  {
    if($category)
    {
      $expense->setCategory($category);
    }

    $value = $request->get('value');
    if($value)
    {
      $expense->setValue($value);
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

    $errors = $this->validator->validate($expense);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $entry = $this->getMatchingEntry($expense->getBudgetYear(), $expense->getMonth(), $category);
    $entry->setReal($request->get('budget_value', ''));

    $errors = $this->validator->validate($entry);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors, 'budget_');
    }

    $this->getDoctrine()->getManager()->persist($entry);

    $this->getDoctrine()->getManager()->persist($expense);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($expense, 200, [], ['groups' => ['expense']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/{year}/expenses/{month}/{id}", methods={"DELETE"}, name="delete_budget_expense")
   * @param BudgetExpense $expense
   * @param Request $request
   * @return Response
   */
  public function delete(BudgetExpense $expense, Request $request)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    if($expense->getCreatorId() !== $user->getId())
    {
      return new JsonResponse(['error' => 'Invalid user ID'], 403);
    }

    $entry = $this->getMatchingEntry($expense->getBudgetYear(), $expense->getMonth(), $expense->getCategory());
    $entry->setReal($request->get('budget_value', ''));

    $errors = $this->validator->validate($entry);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors, 'budget_');
    }

    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->remove($expense);
    $this->getDoctrine()->getManager()->flush();

    return new JsonResponse(['success' => true]);
  }

  /**
   * @return BudgetExpenseRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetExpense::class);
  }

  private function getMatchingEntry(BudgetYear $budgetYear, int $month, Category $category): BudgetEntry
  {
    $entry = $this->getDoctrine()->getRepository(BudgetEntry::class)->findOneBy([
      'budgetYear' => $budgetYear,
      'category' => $category,
      'month' => $month
    ]);

    if(!$entry)
    {
      /** @var Auth0User $user */
      $user = $this->getUser();
      $entry = new BudgetEntry();
      $entry->setBudgetYear($budgetYear);
      $entry->setCategory($category);
      $entry->setMonth($month);
      $entry->setCreatorId($user->getId());
    }

    return $entry;
  }
}
