<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\Budget;
use App\Entity\BudgetAccess;
use App\Repository\BudgetAccessRepository;
use App\Security\User\Auth0User;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BudgetController extends FOSRestController
{
  use ErrorRenderTrait;

  /** @var ValidatorInterface */
  private $validator;
  /** @var SlugifyInterface */
  private $slugify;

  /**
   * @param ValidatorInterface $validator
   * @param SlugifyInterface $slugify
   */
  public function __construct(ValidatorInterface $validator, SlugifyInterface $slugify)
  {
    $this->validator = $validator;
    $this->slugify = $slugify;
  }

  /**
   * @Route("/budgets", name="budgets", methods={"GET"})
   */
  public function index()
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $budgets = $this->getRepository()->findBy(['userId' => $user->getId()]);

    if (empty($budgets)) {
      $budget = new Budget();
      $budget->setUserId($user->getId());
      $access = new BudgetAccess();
      $access->setName('Domowy');
      $access->setSlug('domowy');
      $access->setIsDefault(true);
      $budget->addAccess($access);

      $this->getDoctrine()->getManager()->persist($budget);
      $this->getDoctrine()->getManager()->persist($access);
      $this->getDoctrine()->getManager()->flush();

      $budgets = [$access];
    }

    return $this->json($budgets, 200, [], ['groups' => ['budget']]);
  }

  /**
   * @Route("/budgets", methods={"POST"}, name="new_budget")
   * @param Request $request
   * @return JsonResponse
   */
  public function create(Request $request)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $name = $request->get('name');
    $slug = $this->slugify->slugify($name);
    $access = $this->getRepository()->findOneByOrNew($user, [
      'slug' => $slug,
    ]);

    $access->setName($name);
    $access->setSlug($slug);
    $access->setIsDefault($request->get('make_default') ? true : false);
    $access->setUserId($user->getId());

    $errors = $this->validator->validate($access);

    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($access);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($access, 201, [], ['groups' => ['budget']]);
  }

  /**
   * @Route("/budgets/{budget_slug}", methods={"PATCH"}, name="update_budget")
   * @param BudgetAccess $access
   * @param Request $request
   * @return JsonResponse
   */
  public function update(BudgetAccess $access, Request $request)
  {
    $name = $request->get('name');
    if($name)
    {
      $slug = $this->slugify->slugify($name);
      $access->setName($name);
      $access->setSlug($slug);
    }
    $isDefault = $request->get('make_default');
    if($isDefault !== null)
    {
      $access->setIsDefault($isDefault ? true : false);
    }
    $errors = $this->validator->validate($access);

    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($access);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($access, 200, [], ['groups' => ['budget']]);
  }

  /**
   * @return BudgetAccessRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetAccess::class);
  }
}
