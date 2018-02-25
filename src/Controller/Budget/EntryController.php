<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\BudgetEntry;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntryController extends FOSRestController
{
  /**
   * @Route("/budgets/{year}/entries", methods={"GET"}, name="budget_entries", requirements={"year": "\d{4}"})
   * @param Budget $budget
   * @return JsonResponse
   */
  public function index(Budget $budget)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budget' => $budget]);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route(
   *   "/budgets/{year}/entries/{month}",
   *   methods={"GET"},
   *   name="budget_month_entries",
   *   requirements={"year": "\d{4}", "month": "\d\d?"})
   * @param Budget $budget
   * @return JsonResponse
   */
  public function month(Budget $budget, int $month)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budget' => $budget, 'month' => $month]);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route(
   *   "/budgets/{year}/entries/{month}/{category_id}",
   *   methods={"PUT"},
   *   name="update_budget_entry",
   *   requirements={"year": "\d{4}"}
   * )
   * @ParamConverter("category")
   * @param Budget $budget
   * @param Category $category
   * @param int $month
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function update(Budget $budget, Category $category, int $month, Request $request, ValidatorInterface $validator)
  {
    $entry = $this->getRepository()->findOneByOrNew([
      'budget' => $budget,
      'category' => $category,
      'month' => $month,
    ]);
    $entry->setBudget($budget);
    $entry->setCategory($category);
    $entry->setMonth($month);

    $plan = $request->get('planned');
    if($plan)
    {
      $entry->setPlan((float)$plan);
    }

    $real = $request->get('real');
    if($real)
    {
      $entry->setReal((float)$real);
    }

    $errors = $validator->validate($entry);

    if(count($errors) > 0)
    {
      return $this->json($errors);
    }

    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($entry, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route("/budgets/{year}/entries/{id}", methods={"DELETE"}, name="delete_budget_entry")
   * @param BudgetEntry $entry
   * @return Response
   */
  public function delete(BudgetEntry $entry)
  {
    $this->getDoctrine()->getManager()->remove($entry);
    $this->getDoctrine()->getManager()->flush();

    return new Response();
  }

  /**
   * @return BudgetEntryRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetEntry::class);
  }
}
