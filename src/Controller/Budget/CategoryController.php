<?php
declare(strict_types=1);

namespace App\Controller\Budget;

use App\Controller\Traits\ErrorRenderTrait;
use App\Entity\BudgetAccess;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Security\User\Auth0User;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends FOSRestController
{
  use ErrorRenderTrait;

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
   * @Route("/budgets/{budget_slug}/categories", name="categories", methods={"GET"})
   * @ParamConverter("budget")
   * @param BudgetAccess $access
   * @return JsonResponse
   */
  public function index(BudgetAccess $access)
  {
    return $this->json(
      $this->getRepository()->findBy(['budget' => $access->getBudget()]),
      200,
      [],
      ['groups' => ['category']]
    );
  }

  /**
   * @Route("/budgets/{budget_slug}/categories", methods={"POST"}, name="new_category")
   * @param BudgetAccess $access
   * @param Request $request
   * @return JsonResponse
   */
  public function create(BudgetAccess $access, Request $request)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $parentId = $request->get('parent_id');
    $category = new Category();
    $category->setName($request->get('name'));
    $category->setType($request->get('type'));
    $category->setBudget($access->getBudget());
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

    $errors = $this->validator->validate($category);

    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($category);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($category, 201, [], ['groups' => ['category']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/categories/{category_id}", methods={"PATCH"}, name="update_category")
   * @param Category $category
   * @param Request $request
   * @return JsonResponse
   */
  public function update(Category $category, Request $request)
  {
    $name = $request->get('name');
    if($name)
    {
      $category->setName($name);
    }

    $startedAt = new \DateTime($request->get('year', date('Y')).'-'.$request->get('month', date('m')).'-01');
    if(!$category->getStartedAt() || $category->getStartedAt() > $startedAt)
    {
      $category->setStartedAt($startedAt);
    }

    if($category->getDeletedAt() && $category->getDeletedAt() < $startedAt)
    {
      $category->setDeletedAt(null);
    }

    $errors = $this->validator->validate($category);
    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
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
    $em = $this->getDoctrine()->getManager();
    $deletedAt = new \DateTime($request->get('year').'-'.$request->get('month', '01').'-01');
    $category->setDeletedAt($deletedAt);

    foreach($this->getRepository()->findBy(['parent' => $category]) as $subcategory)
    {
      $subcategory->setDeletedAt($deletedAt);

      if($subcategory->getStartedAt() == $subcategory->getDeletedAt())
      {
        $em->remove($subcategory);
      }
      else
      {
        $em->persist($subcategory);
      }
    }

    if($category->getStartedAt() == $category->getDeletedAt())
    {
      $em->remove($category);
    }
    else
    {
      $em->persist($category);
    }

    $em->flush();

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
