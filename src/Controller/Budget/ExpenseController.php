<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetEntry;
use App\BudgetExpense;
use App\Entity\BudgetReceipt;
use App\Entity\BudgetReceiptItem;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Repository\BudgetReceiptRepository;
use App\Security\User\Auth0User;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
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
    /** @var BudgetReceipt[] $receipts */
    $receipts = $this->getRepository()->findBy(['budgetYear' => $budgetYear, 'month' => $month], ['id' => 'DESC']);
    $items = [];
    foreach ($receipts as $receipt) {
      foreach ($receipt->getItems() as $item) {
        /** @var BudgetReceiptItem $item */
        $expense = new BudgetExpense();
        $expense->setId($item->getId());
        $expense->setBudgetYear($receipt->getBudgetYear());
        $expense->setCategory($item->getCategory());
        $expense->setCreatorId($item->getCreatorId());
        $expense->setDay($receipt->getDay());
        $expense->setDescription($item->getDescription());
        $expense->setMonth($receipt->getMonth());
        $expense->setValue($item->getValue());
        $items[] = $expense;
      }
    }

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
    $receipt = new BudgetReceipt();
    $receipt->setBudgetYear($budgetYear);
    $receipt->setMonth($month);
    $receipt->setDay((int)$request->get('day'));
    $receipt->setCreatorId($user->getId());

    $item = new BudgetReceiptItem();
    $item->setCategory($category);
    $item->setCreatorId($user->getId());
    $item->setDescription($request->get('description', ''));
    $item->setValue($request->get('value', ''));
    $item->setReceipt($receipt);

    $receipt->setItems([$item]);

    $expense = new BudgetExpense();
    $expense->setBudgetYear($receipt->getBudgetYear());
    $expense->setCategory($item->getCategory());
    $expense->setCreatorId($item->getCreatorId());
    $expense->setDay($receipt->getDay());
    $expense->setDescription($item->getDescription());
    $expense->setMonth($receipt->getMonth());
    $expense->setValue($item->getValue());

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
    $this->getDoctrine()->getManager()->persist($receipt);
    $this->getDoctrine()->getManager()->persist($item);
    $this->getDoctrine()->getManager()->flush();

    $expense->setId($item->getId());

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
   * @param BudgetReceiptItem $item
   * @param Category $category
   * @param Request $request
   * @return JsonResponse
   */
  public function update(BudgetReceiptItem $item, Category $category, Request $request)
  {
    $receipt = $item->getReceipt();

    if($category)
    {
      $item->setCategory($category);
    }

    $value = $request->get('value');
    if($value)
    {
      $item->setValue($value);
    }

    $day = $request->get('day');
    if($day)
    {
      $receipt->setDay((int)$day);
    }

    $description = $request->get('description');
    if($description !== null)
    {
      $item->setDescription($description);
    }

    $errors = $this->validator->validate($item);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $entry = $this->getMatchingEntry($receipt->getBudgetYear(), $receipt->getMonth(), $category);
    $entry->setReal($request->get('budget_value', ''));

    $errors = $this->validator->validate($entry);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors, 'budget_');
    }

    $this->getDoctrine()->getManager()->persist($entry);

    $this->getDoctrine()->getManager()->persist($receipt);
    $this->getDoctrine()->getManager()->persist($item);
    $this->getDoctrine()->getManager()->flush();

    $expense = new BudgetExpense();
    $expense->setId($item->getId());
    $expense->setBudgetYear($receipt->getBudgetYear());
    $expense->setCategory($item->getCategory());
    $expense->setCreatorId($item->getCreatorId());
    $expense->setDay($receipt->getDay());
    $expense->setDescription($item->getDescription());
    $expense->setMonth($receipt->getMonth());
    $expense->setValue($item->getValue());

    return $this->json($expense, 200, [], ['groups' => ['expense']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/{year}/expenses/{month}/{id}", methods={"DELETE"}, name="delete_budget_expense")
   * @param BudgetReceiptItem $item
   * @param Request $request
   * @return Response
   */
  public function delete(BudgetReceiptItem $item, Request $request)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    if($item->getCreatorId() !== $user->getId())
    {
      return new JsonResponse(['error' => 'Invalid user ID'], 403); // TODO: Properly translate this message
    }

    $receipt = $item->getReceipt();
    $entry = $this->getMatchingEntry($receipt->getBudgetYear(), $receipt->getMonth(), $item->getCategory());
    $entry->setReal($request->get('budget_value', ''));

    $errors = $this->validator->validate($entry);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors, 'budget_');
    }

    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->remove($item);
    $this->getDoctrine()->getManager()->flush();

    return new JsonResponse(['success' => true]);
  }

  /**
   * @return BudgetReceiptRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetReceipt::class);
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
