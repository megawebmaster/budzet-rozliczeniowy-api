<?php
declare(strict_types=1);

namespace App\Controller\V2\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetReceipt;
use App\Entity\BudgetReceiptItem;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Repository\BudgetReceiptRepository;
use App\Repository\CategoryRepository;
use App\Security\User\Auth0User;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReceiptController extends FOSRestController
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
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}",
   *   name="v2_budget_receipts",
   *   methods={"GET"},
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetYear $budgetYear
   * @param int $month
   *
   * @return JsonResponse
   */
  public function index(BudgetYear $budgetYear, int $month)
  {
    $items = $this->getRepository()->findBy(['budgetYear' => $budgetYear, 'month' => $month], ['id' => 'DESC']);

    return $this->json($items, 200, [], ['groups' => ['receipt']]);
  }

  /**
   * @Route(
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}",
   *   methods={"POST"},
   *   name="new_budget_receipt",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetYear $budgetYear
   * @param int $month
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function create(BudgetYear $budgetYear, int $month, Request $request)
  {
    /** @var Auth0User $user */
    $user    = $this->getUser();
    $receipt = new BudgetReceipt();
    $receipt->setBudgetYear($budgetYear);
    $receipt->setMonth($month);

    $value = $request->get('value');
    $receipt->setDay((int)$value['day']);
    $receipt->setCreatorId($user->getId());

    $categoryRepository = $this->getCategoryRepository();
    $items = array_map(function($itemValue) use ($receipt, $user, $categoryRepository) {
      /** @var Category $category */
      $category = $categoryRepository->find($itemValue['category_id']);
      $item = new BudgetReceiptItem();
      $item->setCategory($category);
      $item->setCreatorId($user->getId());
      $item->setDescription($itemValue['description']);
      $item->setValue($itemValue['value']);
      $item->setReceipt($receipt);

      // TODO: Update correct entry
//      $entry = $this->getMatchingEntry($budgetYear, $month, $category);
//      $entry->setReal($request->get('budget_value', ''));

//      $errors = $this->validator->validate($entry);
//      if (count($errors) > 0) {
//        return $this->renderErrors($errors, 'budget_');
//      }

      $this->getDoctrine()->getManager()->persist($item);
//      $this->getDoctrine()->getManager()->persist($entry);

      return $item;
    }, $value['items']);

    $receipt->setItems($items);

    $errors = $this->validator->validate($receipt);
    if (count($errors) > 0) {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($receipt);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($receipt, 201, [], ['groups' => ['receipt']]);
  }

  /**
   * @Route(
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}/{id}",
   *   methods={"DELETE"},
   *   name="delete_budget_receipt",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetReceipt $receipt
   *
   * @return Response
   * @throws \Exception
   */
  public function delete(BudgetReceipt $receipt)
  {
    $em = $this->getDoctrine()->getManager();
    foreach($receipt->getItems() as $item) {
      $em->remove($item);
    }
    $em->remove($receipt);
    $em->flush();

    return new Response();
  }

  /**
   * @return BudgetReceiptRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetReceipt::class);
  }

  /**
   * @return CategoryRepository
   */
  private function getCategoryRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(Category::class);
  }
}
