<?php
declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\Budget;
use App\Entity\BudgetReceipt;
use App\Entity\BudgetYear;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BudgetReceiptConverter implements ParamConverterInterface
{
  /** @var ManagerRegistry */
  private $registry;
  /** @var TokenStorageInterface */
  private $tokenStorage;

  /**
   * BudgetReceiptConverter constructor.
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

    $id = (int)$request->get('receipt_id');
    $year = (int)$request->get('year');
    $month = (int)$request->get('month');
    $em = $this->registry->getManager();
    $name = $configuration->getName();

    $budgetYearRepository = $em->getRepository(BudgetYear::class);
    $budgetYear = $budgetYearRepository->findOneBy(['budget' => $budget, 'year' => $year]);
    if($budgetYear === null)
    {
      return false;
    }

    $repository = $em->getRepository(BudgetReceipt::class);
    $object = $repository->findOneBy(['budgetYear' => $budgetYear, 'month' => $month, 'id' => $id]);

    if(!$object)
    {
      $request->attributes->set($name, null);
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
    return $configuration->getClass() === BudgetReceipt::class;
  }
}
