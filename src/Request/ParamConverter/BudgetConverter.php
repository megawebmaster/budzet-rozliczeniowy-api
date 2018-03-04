<?php
declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\Budget;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class BudgetConverter implements ParamConverterInterface
{
  /** @var ManagerRegistry */
  private $registry;

  /**
   * CategoryConverter constructor.
   *
   * @param ManagerRegistry $registry
   */
  public function __construct(ManagerRegistry $registry = null)
  {
    $this->registry = $registry;
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
    $year = (int)$request->get('year');
    $name = $configuration->getName();
    $em = $this->registry->getManager();
    $object = $em->getRepository(Budget::class)->findOneBy(['year' => $year]);

    if(!$object)
    {
      $object = new Budget();
      $object->setYear($year);
      $object->setName('Budget');
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
    return $configuration->getClass() === Budget::class;
  }
}
