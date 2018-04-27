<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Entity\Budget;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Security\User\Auth0User;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends FOSRestController
{
  /**
   * @Route("/budgets/{budget_slug}/categories", name="categories", methods={"GET"})
   * @ParamConverter("budget")
   * @param Budget $budget
   * @return JsonResponse
   */
  public function index(Budget $budget)
  {
    return $this->json($this->getRepository()->findBy(['budget' => $budget]), 200, [], ['groups' => ['category']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/categories", methods={"POST"}, name="new_category")
   * @param Budget $budget
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function create(Budget $budget, Request $request, ValidatorInterface $validator)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $parentId = $request->get('parent_id');
    $category = $this->getRepository()->findOneByOrNew([
      'name' => $request->get('name'),
      'type' => $request->get('type'),
      'parent' => $parentId,
      'budget' => $budget
    ]);

    $category->setName($request->get('name'));
    $category->setType($request->get('type'));
    $category->setBudget($budget);
    $category->setCreatorId($user->getId());
    $category->setDeletedAt(null);

    $startedAt = new \DateTime($request->get('year').'-'.$request->get('month', '01').'-01');
    if(!$category->getStartedAt() || $category->getStartedAt() > $startedAt)
    {
      $category->setStartedAt($startedAt);
    }

    if($parentId && $category->getId() !== $parentId)
    {
      /** @var Category $parent */
      $parent = $this->getRepository()->find($parentId);
      $category->setParent($parent);
    }

    $errors = $validator->validate($category);

    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
    }

    $this->getDoctrine()->getManager()->persist($category);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($category, 201, [], ['groups' => ['category']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/categories/{category_id}", methods={"PATCH"}, name="update_category")
   * @param Category $category
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function update(Category $category, Request $request, ValidatorInterface $validator)
  {
    $name = $request->get('name');
    if($name)
    {
      $category->setName($name);
    }
    $errors = $validator->validate($category);

    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
    }

    $this->getDoctrine()->getManager()->persist($category);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($category, 200, [], ['groups' => ['category']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/categories/{category_id}", methods={"DELETE"}, name="delete_category")
   * @param Category $category
   * @param Request $request
   * @return Response
   */
  public function delete(Category $category, Request $request)
  {
    $category->setDeletedAt(new \DateTime($request->get('year').'-'.$request->get('month', '01').'-01'));
    // TODO: Delete all it's children as well
    $this->getDoctrine()->getManager()->persist($category);
    $this->getDoctrine()->getManager()->flush();

    return new Response();
  }

  /**
   * @return CategoryRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(Category::class);
  }
}
