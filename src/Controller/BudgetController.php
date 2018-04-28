<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\Budget;
use App\Repository\BudgetRepository;
use App\Security\User\Auth0User;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
      $budget->setName('Domowy');
      $budget->setSlug('domowy');
      $budget->setIsDefault(true);

      $this->getDoctrine()->getManager()->persist($budget);
      $this->getDoctrine()->getManager()->flush();

      $budgets = [$budget];
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
    $budget = $this->getRepository()->findOneByOrNew([
      'slug' => $slug,
      'userId' => $user->getId(),
    ]);

    $budget->setName($name);
    $budget->setSlug($slug);
    $budget->setIsDefault($request->get('make_default') ? true : false);
    $budget->setUserId($user->getId());

    $errors = $this->validator->validate($budget);

    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($budget);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($budget, 201, [], ['groups' => ['budget']]);
  }

  /**
   * @Route("/budgets/{budget_id}", methods={"PATCH"}, name="update_budget")
   * @param Budget $budget
   * @param Request $request
   * @return JsonResponse
   */
  public function update(Budget $budget, Request $request)
  {
    $name = $request->get('name');
    if($name)
    {
      $slug = $this->slugify->slugify($name);
      $budget->setName($name);
      $budget->setSlug($slug);
    }
    $isDefault = $request->get('make_default');
    if($isDefault !== null)
    {
      $budget->setIsDefault($isDefault ? true : false);
    }
    $errors = $this->validator->validate($budget);

    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($budget);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($budget, 200, [], ['groups' => ['budget']]);
  }

  /**
   * @return BudgetRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(Budget::class);
  }
}
