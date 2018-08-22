<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\BudgetAccess;
use App\Security\User\Auth0User;
use Cocur\Slugify\SlugifyInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BudgetAccessController extends FOSRestController
{
  /** @var SlugifyInterface */
  private $slugify;

  /**
   * @param SlugifyInterface $slugify
   */
  public function __construct(SlugifyInterface $slugify)
  {
    $this->slugify = $slugify;
  }

  /**
   * @Route("/budget-accesses/{id}", name="budget-accesses", methods={"GET"})
   * @param BudgetAccess $budgetAccess
   * @return JsonResponse
   */
  public function one(BudgetAccess $budgetAccess)
  {
    return $this->json($budgetAccess, 200, [], ['groups' => ['budget_access']]);
  }

  /**
   * @Route("/budget-accesses/{id}", methods={"POST"}, name="save_budget_access")
   * @param BudgetAccess $access
   * @param Request $request
   * @return JsonResponse
   */
  public function create(BudgetAccess $access, Request $request)
  {
    $name = $request->get('name');
    if(!$name)
    {
      return $this->json(['error' => 'errors.budget-access.name.empty'], 400);
    }

    /** @var Auth0User $user */
    $user = $this->getUser();
    $slug = $this->slugify->slugify($name);
    $access->setName($name);
    $access->setSlug($slug);
    $access->setUserId($user->getId());

    $this->getDoctrine()->getManager()->persist($access);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($access, 200, [], ['groups' => ['budget']]);
  }
}
