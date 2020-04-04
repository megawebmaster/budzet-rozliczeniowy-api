<?php
declare(strict_types=1);

namespace App\Controller\V2\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetEntry;
use App\Entity\BudgetYear;
use App\Repository\BudgetEntryRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IrregularEntryController extends FOSRestController
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
   *   "/v2/budgets/{budget_slug}/{year}/irregular-entries",
   *   methods={"GET"},
   *   name="v2_budget_irregular_entries",
   *   requirements={"year": "\d{4}"}
   * )
   * @param BudgetYear $budgetYear
   *
   * @return JsonResponse
   */
  public function index(BudgetYear $budgetYear)
  {
    $repository = $this->getRepository();
    $items      = $repository->findIrregularEntries($budgetYear);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @return BudgetEntryRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetEntry::class);
  }
}
