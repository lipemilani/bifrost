<?php

namespace Bifrost\Http\Web\Controllers;

use Illuminate\Http\Request;
use Bifrost\Validation\Validator;
use Bifrost\Services\ApplicationService;
use Bifrost\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests as IlluminateValidatesRequests;

abstract class Controller extends BaseController
{
  use
    AuthorizesRequests,
    DispatchesJobs,
    IlluminateValidatesRequests,
    ValidatesRequests;

  /**
   * @var Request
   */
  protected Request $request;

  /**
   * @var ApplicationService
   */
  protected ApplicationService $service;

  /**
   * @var Validator
   */
  protected Validator $validator;

  /**
   * Controller constructor.
   * @param ApplicationService $service
   * @param Validator|null $validator
   */
  public function __construct(ApplicationService $service, ?Validator $validator = null)
  {
    $this->service = $service;
    $this->validator = $validator;
  }

  /**
   * @return ApplicationService
   */
  public function getService(): ApplicationService
  {
    return $this->service;
  }

  /**
   * @return null|Validator
   */
  public function getValidator(): ?Validator
  {
    return $this->validator;
  }

  /**
   * Execute an action on the controller.
   *
   * @param  string $method
   * @param  array $parameters
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function callAction($method, $parameters)
  {
    # Check ACL
    if (!$this->aclValidation($method)) {
      return $this->errorResponse([$this->getNotAuthorizedError()], 403);
    }

    # Check Attributes
    $validation = $this->dataValidation($method, $parameters);
    if (!blank($validation)) {
      return $this->errorResponse($validation, 422);
    }

    # Call Method
    return call_user_func_array([$this, $method], $parameters);
  }

}
