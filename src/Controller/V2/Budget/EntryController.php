<?php
declare(strict_types=1);

namespace App\Controller\V2\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetEntry;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;
use App\Security\User\Auth0User;
use App\Service\BudgetEntryCreator;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntryController extends FOSRestController
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
   *   "/v2/budgets/{budget_slug}/{year}/entries/{month}",
   *   methods={"GET"},
   *   name="v2_budget_month_entries",
   *   requirements={"year": "\d{4}", "month": "\d\d?"}
   * )
   * @param BudgetYear $budgetYear
   * @param int $month
   *
   * @return JsonResponse
   */
  public function index(BudgetYear $budgetYear, int $month)
  {
    $repository = $this->getRepository();
    $items      = $repository->findEntries($budgetYear, $month);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route(
   *   "/v2/budgets/{budget_slug}/{year}/entries/{month}/{category_id}",
   *   methods={"PUT"},
   *   name="v2_update_budget_entry",
   *   requirements={"year": "\d{4}", "month": "\d\d?"}
   * )
   * @param BudgetYear $budgetYear
   * @param Category $category
   * @param int $month
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function update(BudgetYear $budgetYear, Category $category, int $month, Request $request)
  {
    /** @var Auth0User $user */
    $user  = $this->getUser();
    $value = $request->get('value');

    $creator = new BudgetEntryCreator($this->getRepository(), $budgetYear, $category, $user);
    $entry   = $creator->findAndUpdate(
      $request->get('web_crypto'),
      $category->isIrregular() ? null : $month,
      $value['plan'],
      $category->isIrregular() ? '' : $value['real']
    );
    $errors  = $this->validator->validate($entry);

    if (count($errors) > 0) {
      return $this->renderErrors($errors);
    }

    $em = $this->getDoctrine()->getManager();
    if ($category->isIrregular() && ! empty($value['plan_monthly'])) {
      for ($month = 1; $month <= 12; $month++) {
        $item = $creator->findAndUpdate($request->get('web_crypto'), $month, $value['plan_monthly']);
        $em->persist($item);
      }
    }

    $em->persist($entry);
    $em->flush();

    return $this->json($entry, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @return BudgetEntryRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetEntry::class);
  }
}
