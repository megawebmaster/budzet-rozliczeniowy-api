<?php
declare(strict_types=1);

namespace App\Controller\V2\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetEntry;
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
    $receipt->setShop($value['shop']);
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

      $this->getDoctrine()->getManager()->persist($item);

      return $item;
    }, $value['items']);

    $receipt->setItems($items);

    $em = $this->getDoctrine()->getManager();
    foreach($value['budget_values'] as $budgetValue) {
      $entry = $this->getEntry($budgetYear, $month, $budgetValue['category_id']);
      $entry->setReal($budgetValue['value']);

      $em->persist($entry);
    }

    $errors = $this->validator->validate($receipt);
    if (count($errors) > 0) {
      return $this->renderErrors($errors);
    }

    $em->persist($receipt);
    $em->flush();

    return $this->json($this->getRepository()->find($receipt->getId()), 201, [], ['groups' => ['receipt']]);
  }

  /**
   * @Route(
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}/{receipt_id}",
   *   methods={"PUT"},
   *   name="update_budget_receipt",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetReceipt $receipt
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function update(BudgetReceipt $receipt, Request $request)
  {
    $value = $request->get('value');
    if($value['day'])
    {
      $receipt->setDay((int)$value['day']);
    }

    if($value['shop'] !== null)
    {
      $receipt->setShop($value['shop']);
    }

    $errors = $this->validator->validate($receipt);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($receipt);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($receipt, 200, [], ['groups' => ['receipt']]);
  }

  /**
   * @Route(
   *   "/v2/budgets/{budget_slug}/{year}/receipts/{month}/{receipt_id}",
   *   methods={"DELETE"},
   *   name="delete_budget_receipt",
   *   requirements={"year": "\d{4}", "month": "\d{1,2}"}
   * )
   * @param BudgetReceipt $receipt
   * @param Request $request
   *
   * @return Response
   */
  public function delete(BudgetReceipt $receipt, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $value = $request->get('value');
    foreach($value['budget_values'] as $budgetValue) {
      $entry = $this->getEntry($receipt->getBudgetYear(), $receipt->getMonth(), $budgetValue['category_id']);
      $entry->setReal($budgetValue['value']);

      $em->persist($entry);
    }

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
