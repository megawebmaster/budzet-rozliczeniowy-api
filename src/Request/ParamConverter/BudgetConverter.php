<?php
declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\Budget;
use App\Security\User\Auth0User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BudgetConverter implements ParamConverterInterface
{
  /** @var ManagerRegistry */
  private $registry;
  /** @var TokenStorageInterface */
  private $tokenStorage;

  /**
   * CategoryConverter constructor.
   *
   * @param TokenStorageInterface $tokenStorage
   * @param ManagerRegistry $registry
   */
  public function __construct(TokenStorageInterface $tokenStorage, ManagerRegistry $registry = null)
  {
    $this->registry = $registry;
    $this->tokenStorage = $tokenStorage;
  }

  /**
   * Stores the object in the request.
   *
   * @param Request $request
   * @param ParamConverter $configuration Contains the name, class and options of the object
   * @return bool True if the object has been successfully set, else false
   */
  public function apply(Request $request, ParamConverter $configuration)
  {
    $token = $this->tokenStorage->getToken();
    if($token === null)
    {
      return false;
    }
    /** @var Auth0User $user */
    $user = $token->getUser();
    $budgetId = $request->get('budget_id');
    $budgetSlug = $request->get('budget_slug');
    $em = $this->registry->getManager();
    $repository = $em->getRepository(Budget::class);
    $name = $configuration->getName();

    if ($budgetId !== null)
    {
      $object = $repository->findOneBy(['id' => $budgetId, 'userId' => $user->getId()]);
    }
    else
    {
      $object = $repository->findOneBy(['slug' => $budgetSlug, 'userId' => $user->getId()]);
    }

    if(!$object)
    {
      return false;
    }

    $request->attributes->set($name, $object);

    return true;
  }

  /**
   * Checks if the object is supported.
   *
   * @param ParamConverter $configuration
   * @return bool True if the object is supported, else false
   */
  public function supports(ParamConverter $configuration)
  {
    return $configuration->getClass() === Budget::class;
  }
}
