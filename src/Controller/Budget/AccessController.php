<?php
declare(strict_types=1);

namespace App\Controller\Budget;

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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AccessController extends FOSRestController
{
  use ErrorRenderTrait;

  /** @var ValidatorInterface */
  private $validator;
  /** @var SlugifyInterface */
  private $slugify;
  /** @var TranslatorInterface */
  private $translator;

  /**
   * @param ValidatorInterface $validator
   * @param SlugifyInterface $slugify
   * @param TranslatorInterface $translator
   */
  public function __construct(ValidatorInterface $validator, SlugifyInterface $slugify, TranslatorInterface $translator)
  {
    $this->validator = $validator;
    $this->slugify = $slugify;
    $this->translator = $translator;
  }

  /**
   * @Route("/budgets/{budget_slug}/accesses", name="budget_access", methods={"GET"})
   * @param Budget $budget
   * @return JsonResponse
   */
  public function index(Budget $budget)
  {
    $accesses = $this->getRepository()->findBy(['budget' => $budget]);

    return $this->json($accesses, 200, [], ['groups' => ['budget_access']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/accesses", methods={"POST"}, name="new_budget_access")
   * @param Request $request
   * @param Budget $budget
   * @param \Swift_Mailer $mailer
   * @return JsonResponse
   */
  public function create(Request $request, Budget $budget, \Swift_Mailer $mailer)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    list($username) = explode('@', $user->getUsername());
    $simplifiedEmail = $username[0].'…'.$username[strlen($username)-1];

    $access = new BudgetAccess();
    $access->setBudget($budget);
    $access->setRecipient($request->get('recipient'));
    $access->setName($this->translator->trans('Shared budget (%name%)', ['%name%' => $simplifiedEmail]));
    $access->setSlug($this->slugify->slugify($access->getName()));
    $access->setIsDefault(false);

    $errors = $this->validator->validate($access);

    if(count($errors) > 0)
    {
      return $this->renderErrors($errors);
    }

    $this->getDoctrine()->getManager()->persist($access);
    $this->getDoctrine()->getManager()->flush();

    try
    {
      $message = (new \Swift_Message($this->translator->trans("I've shared a budget with you!")))
        ->setFrom('no-reply@simplybudget.it', 'SimplyBudget.it')
        ->setReplyTo($user->getUsername())
        ->setTo($request->get('email'))
        ->setBody(
          $this->renderView(
            'emails/budget_shared.html.twig',
            [
              'email' => $user->getUsername(),
              'link' => $this->container->getParameter('app.url').'/budget/shared/'.$access->getId()
            ]
          ),
          'text/html'
        )
      ;

      if($mailer->send($message) !== 1)
      {
        $this->getDoctrine()->getManager()->remove($access);
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['error' => 'errors.budget-share.failure'], 500);
      }
    } catch(\Exception $e) {
      $this->getDoctrine()->getManager()->remove($access);
      $this->getDoctrine()->getManager()->flush();

      return $this->json(['error' => 'errors.budget-share.invalid-email'], 500);
    }

    return $this->json($access, 201, [], ['groups' => ['budget_access']]);
  }

  /**
   * @Route("/budgets/{budget_slug}/accesses/{id}", methods={"DELETE"}, name="delete_budget_access")
   * @param int $id
   * @return Response
   */
  public function delete(int $id)
  {
    /** @var Auth0User $user */
    $user = $this->getUser();
    $access = $this->getRepository()->find($id);
    if($access->getUserId() === $user->getId())
    {
      return $this->json(['error' => 'errors.budget-share.cannot-remove-yourself'], 400);
    }
    if($access->getBudget()->getUserId() === $access->getUserId())
    {
      return $this->json(['error' => 'errors.budget-share.cannot-remove-owner'], 400);
    }

    $this->getDoctrine()->getManager()->remove($access);
    $this->getDoctrine()->getManager()->flush();

    return new Response();
  }

  /**
   * @return BudgetAccessRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(BudgetAccess::class);
  }
}
