<?php
declare(strict_types=1);

namespace App\Controller\V2\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetEntry;
use App\Entity\BudgetReceipt;
use App\Entity\BudgetReceiptItem;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Security\User\Auth0User;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReceiptItemController extends FOSRestController
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
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}/{receipt_id}/items",
   *   methods={"POST"},
   *   name="new_budget_receipt_item",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @ParamConverter("category")
   * @param Category $category
   * @param BudgetReceipt $receipt
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function create(Category $category, BudgetReceipt $receipt, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    /** @var Auth0User $user */
    $user = $this->getUser();

    $value = $request->get('value');

    $item = new BudgetReceiptItem();
    $item->setCategory($category);
    $item->setCreatorId($user->getId());
    $item->setDescription($value['description']);
    $item->setValue($value['value']);
    $item->setReceipt($receipt);

    foreach($value['budget_values'] as $budgetValue) {
      $entry = $this->getEntry($receipt->getBudgetYear(), $receipt->getMonth(), $budgetValue['category_id']);
      $entry->setReal($budgetValue['value']);

      $em->persist($entry);
    }

    $errors = $this->validator->validate($item);
    if (count($errors) > 0) {
      return $this->renderErrors($errors);
    }

    $em->persist($item);
    $em->flush();

    return $this->json($item, 201, [], ['groups' => ['receipt_item']]);
  }

  /**
   * @Route(
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}/{receipt_id}/items/{id}",
   *   methods={"PUT"},
   *   name="update_budget_receipt_item",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetReceipt $receipt
   * @param Category $category
   * @param BudgetReceiptItem $item
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function update(BudgetReceipt $receipt, Category $category, BudgetReceiptItem $item, Request $request)
  {
    if ($receipt->getItems()->indexOf($item) === false)
    {
      // TODO: Throw proper errors here
      return $this->json('', 400);
    }

    $em = $this->getDoctrine()->getManager();
    $item->setCategory($category);
    $value = $request->get('value');

    if($value['value'])
    {
      $item->setValue($value['value']);
    }

    if($value['description'])
    {
      $item->setDescription($value['description']);
    }

    $errors = $this->validator->validate($item);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    foreach($value['budget_values'] as $budgetValue) {
      $entry = $this->getEntry($receipt->getBudgetYear(), $receipt->getMonth(), $budgetValue['category_id']);
      $entry->setReal($budgetValue['value']);

      $em->persist($entry);
    }

    $em->persist($item);
    $em->flush();

    return $this->json($item, 200, [], ['groups' => ['receipt_item']]);
  }

  /**
   * @Route(
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}/{receipt_id}/items/{id}",
   *   methods={"DELETE"},
   *   name="delete_budget_receipt_item",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetReceipt $receipt
   * @param BudgetReceiptItem $item
   * @param Request $request
   *
   * @return Response
   */
  public function delete(BudgetReceipt $receipt, BudgetReceiptItem $item, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $value = $request->get('value');
    foreach($value['budget_values'] as $budgetValue) {
      $entry = $this->getEntry($receipt->getBudgetYear(), $receipt->getMonth(), $budgetValue['category_id']);
      $entry->setReal($budgetValue['value']);

      $em->persist($entry);
    }
    if ($receipt->getItems()->indexOf($item) !== false) {
      $em->remove($item);
      $em->flush();
    }

    return new Response();
  }

  // TODO: Extract this function with updating (from Request) to a separate service
  // TODO: Replace in ReceiptController and ExpenseController too
  private function getEntry(BudgetYear $budgetYear, int $month, int $categoryId): BudgetEntry
  {
    /** @var Category $category */
    $category = $this->getDoctrine()->getRepository(Category::class)->find($categoryId);
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
