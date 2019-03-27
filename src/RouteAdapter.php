<?php

namespace Hiraeth\Chariot;

use Hiraeth\Routing;
use Hiraeth\Utils\Signal;
use Awesomite\Chariot\InternalRouteInterface as Route;

/**
 *
 */
class RouteAdapter implements Routing\AdapterInterface
{
	/**
	 *
	 */
	public function __construct(Signal $signal)
	{
		$this->signal = $signal;
	}


	/**
	 *
	 */
	public function __invoke(Routing\Resolver $resolver): callable
	{
		$route      = $resolver->getTarget();
		$handler    = $this->signal->resolve($route->getHandler());

		$resolver->setParameters($route->getParams());

		return $handler;
	}


	/**
	 *
	 */
	public function match(Routing\Resolver $resolver): bool
	{
		return $resolver->getTarget() instanceof Route;
	}
}
