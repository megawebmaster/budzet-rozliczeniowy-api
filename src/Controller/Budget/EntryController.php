<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\BudgetEntry;
use App\Entity\BudgetYear;
use App\Entity\Category;
use App\Repository\BudgetEntryRepository;
use App\Security\User\Auth0User;
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
   *   "/budgets/{budget_slug}/{year}/entries",
   *   methods={"GET"},
   *   name="budget_entries",
   *   requirements={"year": "\d{4}"}
   * )
   * @param BudgetYear $budgetYear
   * @return JsonResponse
   */
  public function index(BudgetYear $budgetYear)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budgetYear' => $budgetYear]);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * @Route(
   *   "/budgets/{budget_slug}/{year}/entries/{month}",
   *   methods={"GET"},
   *   name="budget_month_entries",
   *   requirements={"year": "\d{4}", "month": "\d\d?"})
   * @param BudgetYear $budgetYear
   * @param int $month
   * @return JsonResponse
   */
  public function month(BudgetYear $budgetYear, int $month)
  {
    $repository = $this->getRepository();
    $items = $repository->findBy(['budgetYear' => $budgetYear, 'month' => $month]);

    return $this->json($items, 200, [], ['groups' => ['entry']]);
  }

  /**
   * TODO: Use `month` parameter as well here to keep URLs consistent
   * @Route(
   *   "/budgets/{budget_slug}/{year}/entries/{category_id}",
   *   methods={"PUT"},
   *   name="update_budget_entry",
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
    $entry = $creator->findAndUpdate(
      $request->get('month'),
      $request->get('planned', ''),
      $request->get('real', '')
    );
    $errors = $this->validator->validate($entry);

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
   * TODO: Use `month` parameter as well here to keep URLs consistent
   * @Route("/budgets/{budget_slug}/{year}/entries/{id}", methods={"DELETE"}, name="delete_budget_entry")
   * @param BudgetEntry $entry
   * @return Response
   */
  public function delete(BudgetEntry $entry)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    if($entry->getCreatorId() !== $user->getId())
    {
      return new Response('', 403);
    }

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
