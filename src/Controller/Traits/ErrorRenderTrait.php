<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ErrorRenderTrait
{
  protected function renderErrors(ConstraintViolationListInterface $errors, $prefix = ''): JsonResponse
  {
    $result = [];
    foreach($errors as $error)
    {
      $result[$prefix.$error->getPropertyPath()] = $error->getMessage();
    }

    return $this->json($result, 400);
  }
}
