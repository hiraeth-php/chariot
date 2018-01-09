<?php

namespace Hiraeth\Chariot;

use Exception;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Awesomite\Chariot\RouterInterface as Router;
use Awesomite\Chariot\Exceptions\HttpException;
use Hiraeth\Chariot\Resolver;
use Hiraeth;

/**
 *
 */
class RelayMiddleware
{
	/**
	 *
	 */
	public function __construct(Hiraeth\Application $app, Router $router, Resolver $resolver)
	{
		$this->app      = $app;
		$this->router   = $router;
		$this->resolver = $resolver;
	}


	/**
	 *
	 */
	public function __invoke(Request $request, Response $response, callable $next)
	{
		try {
			$route    = $this->router->match($request->getMethod(), $request->getURI()->getPath());
			$response = $response->withStatus(200);

			return $next($request, $this->resolver->run($this->router, $route, $request, $response));

		} catch (Exception $e) {
			if ($e instanceof HttpException) {
				return $response->withStatus($e->getCode());

			} else {
				if ($this->app->getEnvironment('DEBUG')) {
					throw $e;
				}

				return $response->withStatus(500);
			}
		}
	}
}
