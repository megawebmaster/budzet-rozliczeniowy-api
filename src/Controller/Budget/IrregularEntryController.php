<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetEntry;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;
use App\Security\User\Auth0User;
use App\Service\BudgetEntryCreator;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
   *   "/budgets/{budget_slug}/{year}/irregular",
   *   methods={"GET"},
   *   name="irregular_budget_entries",
   *   requirements={"year": "\d{4}"}
   * )
   * @param BudgetYear $budgetYear
   * @return JsonResponse
   */
  public function index(BudgetYear $budgetYear)
  {
    $items = $this->getRepository()->getIrregularEntries($budgetYear);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route(
   *   "/budgets/{budget_slug}/{year}/irregular/{category_id}",
   *   methods={"PUT"},
   *   name="update_irregular_budget_entry",
   *   requirements={"year": "\d{4}"}
   * )
   * @ParamConverter("category")
   * @param BudgetYear $budgetYear
   * @param Category $category
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function update(BudgetYear $budgetYear, Category $category, Request $request)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $creator = new BudgetEntryCreator($this->getRepository(), $budgetYear, $category, $user);
    $plan = $request->get('plan', '');
    $entry = $creator->findAndUpdate(false, null, $plan);

    $errors = $this->validator->validate($entry);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $em = $this->getDoctrine()->getManager();
    $em->persist($entry);

    $plan = $request->get('plan_monthly', '');
    for($month = 1; $month <= 12; $month++)
    {
      $item = $creator->findAndUpdate(false, $month, $plan);

      $errors = $this->validator->validate($item);
      if(count($errors) > 0)
      {
        return $this->renderErrors($errors, 'budget_');
      }

      $em->persist($item);
    }

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
