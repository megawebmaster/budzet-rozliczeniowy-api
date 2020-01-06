<?php
declare(strict_types=1);

namespace App\Controller\V2\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetReceipt;
use App\Entity\BudgetYear;
use App\Repository\BudgetReceiptRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
   * @return BudgetReceiptRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetReceipt::class);
  }
}
