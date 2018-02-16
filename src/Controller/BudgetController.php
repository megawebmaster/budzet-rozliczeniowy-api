<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Budget;
use App\Repository\BudgetRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class BudgetController extends Controller
{
  /**
   * @Route("/budgets", name="budgets", methods={"GET"})
   */
  public function index()
  {
    return $this->json($this->getRepository()->getAvailableYears());
  }

  /**
   * @Route("/budgets/{year}", name="budget", methods={"GET"}, requirements={"year": "\d{4}"})
   * @param int $year
   * @return JsonResponse
   */
  public function show(int $year)
  {
    $repository = $this->getRepository();
    $budget = $repository->findOneBy(['year' => $year]);

    if (!$budget)
    {
      $repository->create($year);
    }

    return $this->json($budget, 200, [], ['groups' => ['budget']]);
  }

  /**
   * @return BudgetRepository
   */
  private function getRepository(): ObjectRepository
  {
    return $this->getDoctrine()->getRepository(Budget::class);
  }
}
