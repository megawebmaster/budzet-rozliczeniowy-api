<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\BudgetEntry;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;
use App\Service\BudgetEntryCreator;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IrregularEntryController extends FOSRestController
{
  /**
   * @Route(
   *   "/budgets/{year}/irregular",
   *   methods={"GET"},
   *   name="irregular_budget_entries",
   *   requirements={"year": "\d{4}"}
   * )
   * @param Budget $budget
   * @return JsonResponse
   */
  public function index(Budget $budget)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budget' => $budget, 'month' => null]);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route(
   *   "/budgets/{year}/irregular/{category_id}",
   *   methods={"PUT"},
   *   name="update_irregular_budget_entry",
   *   requirements={"year": "\d{4}"}
   * )
   * @ParamConverter("category")
   * @param Budget $budget
   * @param Category $category
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function update(Budget $budget, Category $category, Request $request, ValidatorInterface $validator)
  {
    $creator = new BudgetEntryCreator($this->getRepository(), $budget, $category);
    $plan = $request->get('planned');
    $real = $request->get('real');
    $entry = $creator->findAndUpdate(null, $plan, $real);
    $errors = $validator->validate($entry);

    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
    }

    $em = $this->getDoctrine()->getManager();
    $em->persist($entry);

    for($month = 1; $month <= 12; $month++)
    {
      $item = $creator->findAndUpdate($month, $plan ? (float)$plan / 10.0 : 0.0, $real ? (float)$real / 10.0 : 0.0);
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
