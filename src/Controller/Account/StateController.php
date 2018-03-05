<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Entity\Account;
use App\Entity\AccountState;
use App\Repository\AccountStateRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StateController extends FOSRestController
{
  /**
   * @Route(
   *   "/accounts/{id}/{month}",
   *   methods={"PUT"},
   *   name="update_account_state",
   *   requirements={"month": "\d?\d"}
   * )
   * @param Account $account
   * @param int $month
   * @param Request $request
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  public function update(Account $account, int $month, Request $request, ValidatorInterface $validator)
  {
    $state = $this->getRepository()->findOneByOrNew([
      'account' => $account,
      'month' => $month,
    ]);
    $state->setAccount($account);
    $state->setMonth($month);
    $state->setValue((float)$request->get('value'));
    $errors = $validator->validate($state);

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
    $em->persist($state);
    $em->flush();

    return $this->json($state, 200, [], ['groups' => ['account_state']]);
  }

  /**
   * @return AccountStateRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(AccountState::class);
  }
}
