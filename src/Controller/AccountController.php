<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AccountController extends FOSRestController
{
  /**
   * @Route("/accounts/{year}", name="accounts")
   * @param int $year
   * @return JsonResponse
   */
  public function index(int $year)
  {
    $repository = $this->getRepository();
    $items = $repository->findForYear($year);

    return $this->json($items, 200, [], ['groups' => ['account']]);
  }

  /**
   * @Route("/accounts", methods={"POST"}, name="new_account")
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function create(Request $request, ValidatorInterface $validator)
  {
    $name = $request->get('name');
    $year = (int)$request->get('year');
    $account = $this->getRepository()->findOneOrNew($year, $name);

    $account->setName($name);
    $account->setDescription($request->get('description'));
    $account->setStartedAt(new \DateTime($year.'-01-01'));
    $account->setDeletedAt(null);

    $errors = $validator->validate($account);

    if(count($errors) > 0)
    {
      $result = [];
      foreach($errors as $error)
      {
        $result[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json($result);
    }

    $this->getDoctrine()->getManager()->persist($account);
    $this->getDoctrine()->getManager()->flush();

    return $this->json($account, 201, [], ['groups' => ['account']]);
  }

  /**
   * @return AccountRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(Account::class);
  }
}
