<?php
declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\Category;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 */
class CategoryConverter implements ParamConverterInterface
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
    $name = $configuration->getName();
    $em = $this->registry->getManager();
    $object = $em->getRepository(Category::class)->find($request->get('category_id'));

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
    return $configuration->getClass() === Category::class && $configuration->getName() === 'category';
  }
}
