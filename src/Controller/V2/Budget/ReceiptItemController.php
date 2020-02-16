<?php
declare(strict_types=1);

namespace App\Controller\V2\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetReceipt;
use App\Entity\BudgetReceiptItem;
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
    /** @var Auth0User $user */
    $user = $this->getUser();

    $value = $request->get('value');

    $item = new BudgetReceiptItem();
    $item->setCategory($category);
    $item->setCreatorId($user->getId());
    $item->setDescription($value['description']);
    $item->setValue($value['value']);
    $item->setReceipt($receipt);

//    // TODO: Update correct entry
//    $entry = $this->getMatchingEntry($budgetYear, $month, $category);
//    $entry->setReal($request->get('budget_value', ''));
//
//    $errors = $this->validator->validate($entry);
//    if (count($errors) > 0) {
//      return $this->renderErrors($errors, 'budget_');
//    }

    $errors = $this->validator->validate($item);
    if (count($errors) > 0) {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($item);
//    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->flush();

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

//    // TODO: Update correct entry
//    $entry = $this->getMatchingEntry($budgetYear, $month, $category);
//    $entry->setReal($request->get('budget_value', ''));
//
//    $errors = $this->validator->validate($entry);
//    if (count($errors) > 0) {
//      return $this->renderErrors($errors, 'budget_');
//    }

    $this->getDoctrine()->getManager()->persist($item);
//    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->flush();

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
   *
   * @return Response
   */
  public function delete(BudgetReceipt $receipt, BudgetReceiptItem $item)
  {
    $em = $this->getDoctrine()->getManager();
    if ($receipt->getItems()->indexOf($item) !== false) {
      $em->remove($item);
      $em->flush();
    }

    return new Response();
  }
}
