<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends Controller
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
    /** @var Category $parent */
    $parent = $this->getRepository()->find($request->get('parent_id'));
    $category = new Category();
    $category->setName($request->get('name'));
    $category->setType($request->get('type'));
    $category->setParent($parent);
    $errors = $validator->validate($category);

    if (count($errors) > 0) {
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
    if ($request->request->has('name'))
    {
      $category->setName($request->get('name'));
    }
    $errors = $validator->validate($category);

    if (count($errors) > 0) {
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
    $this->getDoctrine()->getManager()->remove($category);
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
