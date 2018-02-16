<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\BudgetEntry;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntryController extends Controller
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
   *   "/budgets/{year}/entries",
   *   methods={"POST"},
   *   name="new_budget_entry",
   *   requirements={"year": "\d{4}"}
   * )
   * @ParamConverter("category")
   * @param Budget $budget
   * @param Category $category
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function create(Budget $budget, Category $category, Request $request, ValidatorInterface $validator)
  {
    $entry = new BudgetEntry();
    $entry->setBudget($budget);
    $entry->setCategory($category);
    $entry->setMonth((int)$request->get('month'));
    $entry->setPlan((float)$request->get('plan'));
    $entry->setReal((float)$request->get('real'));
    $errors = $validator->validate($entry);

    if (count($errors) > 0) {
      return $this->json($errors);
    }

    $this->getDoctrine()->getManager()->persist($entry);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($entry, 201, [], ['groups' => ['entry']]);
  }

  /**
   * @Route(
   *   "/budgets/{year}/entries/{id}",
   *   methods={"PUT"},
   *   name="update_budget_entry",
   *   requirements={"year": "\d{4}"}
   * )
   * @param BudgetEntry $entry
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function update(BudgetEntry $entry, Request $request, ValidatorInterface $validator)
  {
    if ($request->request->has('plan'))
    {
      $entry->setPlan((float)$request->get('plan'));
    }
    if ($request->request->has('real'))
    {
      $entry->setReal((float)$request->get('real'));
    }
    $errors = $validator->validate($entry);

    if (count($errors) > 0) {
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
