<?php
declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\Budget;
use App\Entity\BudgetAccess;
use App\Entity\BudgetYear;
use App\Security\User\Auth0User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BudgetYearConverter implements ParamConverterInterface
{
  /** @var ManagerRegistry */
  private $registry;
  /** @var TokenStorageInterface */
  private $tokenStorage;

  /**
   * BudgetYearConverter constructor.
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
    $budget = $this->registry->getManager()->getRepository(Budget::class)->getFromRequest($request);
    if($budget === null)
    {
      return false;
    }

    /** @var int $year */
    $year = (int)$request->get('year');
    $em = $this->registry->getManager();
    $name = $configuration->getName();
    $repository = $em->getRepository(BudgetYear::class);
    $object = $repository->findOneBy(['budget' => $budget, 'year' => $year]);

    if(!$object)
    {
      $object = new BudgetYear();
      $object->setBudget($budget);
      $object->setYear($year);
      $em->persist($object);
      $em->flush();
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
    return $configuration->getClass() === BudgetYear::class;
  }
}
