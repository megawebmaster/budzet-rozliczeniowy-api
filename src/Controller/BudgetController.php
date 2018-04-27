<?php
declare(strict_types=1);

namespace App\Controller;

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
  /**
   * @Route("/budgets", name="budgets", methods={"GET"})
   */
  public function index()
  {
    /** @var Auth0User $user */
    $user = $this->getUser();

    return $this->json(
      $this->getRepository()->findBy(['userId' => $user->getId()]),
      200,
      [],
      ['groups' => ['budget']]
    );
  }

  /**
   * @Route("/budgets", methods={"POST"}, name="new_budget")
   * @param Request $request
   * @param ValidatorInterface $validator
   * @param SlugifyInterface $slugify
   * @return JsonResponse
   */
  public function create(Request $request, ValidatorInterface $validator, SlugifyInterface $slugify)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $name = $request->get('name');
    $slug = $slugify->slugify($name);
    $budget = $this->getRepository()->findOneByOrNew([
      'slug' => $slug,
      'userId' => $user->getId(),
    ]);

    $budget->setName($name);
    $budget->setSlug($slug);
    $budget->setIsDefault($request->get('make_default') ? true : false);
    $budget->setUserId($user->getId());

    $errors = $validator->validate($budget);

    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
    }

    $this->getDoctrine()->getManager()->persist($budget);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($budget, 201, [], ['groups' => ['budget']]);
  }

  /**
   * @Route("/budgets/{budget_id}", methods={"PATCH"}, name="update_budget")
   * @param Budget $budget
   * @param Request $request
   * @param ValidatorInterface $validator
   * @param SlugifyInterface $slugify
   * @return JsonResponse
   */
  public function update(Budget $budget, Request $request, ValidatorInterface $validator, SlugifyInterface $slugify)
  {
    $name = $request->get('name');
    if($name)
    {
      $slug = $slugify->slugify($name);
      $budget->setName($name);
      $budget->setSlug($slug);
    }
    $isDefault = $request->get('make_default');
    if($isDefault !== null)
    {
      $budget->setIsDefault($isDefault ? true : false);
    }
    $errors = $validator->validate($budget);

    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
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
