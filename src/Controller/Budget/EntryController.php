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
   * @param int $month
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
   *   "/budgets/{year}/entries/{category_id}",
   *   methods={"PUT"},
   *   name="update_budget_entry",
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
    $entry = $creator->findAndUpdate(
      $request->get('month'),
      $request->get('planned'),
      $request->get('real')
    );
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
    $em->flush();

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
