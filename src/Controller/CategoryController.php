<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends FOSRestController
{
  /**
   * @Route("/categories", name="categories", methods={"GET"})
   */
  public function index()
  {
    return $this->json($this->getRepository()->findAll(), 200, [], ['groups' => ['category']]);
  }

  /**
   * @Route("/categories", methods={"POST"}, name="new_category")
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function create(Request $request, ValidatorInterface $validator)
  {
    $category = new Category();
    $category->setName($request->get('name'));
    $category->setType($request->get('type'));

    $parentId = $request->get('parent_id', null);
    if($parentId)
    {
      /** @var Category $parent */
      $parent = $this->getRepository()->find($parentId);
      $category->setParent($parent);
    }

    $errors = $validator->validate($category);

    if(count($errors) > 0)
    {
      return $this->json($errors);
    }

    $this->getDoctrine()->getManager()->persist($category);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($category, 201, [], ['groups' => ['category']]);
  }

  /**
   * @Route("/categories/{category_id}", methods={"PUT"}, name="update_category")
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
      return $this->json($errors);
    }

    $this->getDoctrine()->getManager()->persist($category);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($category, 200, [], ['groups' => ['category']]);
  }

  /**
   * @Route("/categories/{category_id}", methods={"DELETE"}, name="delete_category")
   * @param Category $category
   * @return Response
   */
  public function delete(Category $category)
  {
    if($category->isDeleted())
    {
      throw $this->createNotFoundException();
    }

    $category->setDeletedAt(new \DateTime());
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
