<?php

namespace Hiraeth\Chariot;

use Hiraeth;
use Hiraeth\Routing\Route;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
class RouterProxy implements Hiraeth\Routing\RouterInterface
{
	/**
	 *
	 */
	public function __construct(PatternRouter $router)
	{
		$this->router = $router;
	}


	/**
	 * Match an incoming request and return a route target.
	 *
	 * If the route cannot be matched, this method should throw a `RouterException` with an
	 * exception code equal to the HTTP 1.1 equivalent of the error.
	 *
	 * @access public
	 * @param Request $request The server request to try and match against a route
	 * @param Response $response The default response to be modified in the event of errors
	 * @return Route The route to run
	 */
	public function match(Request $request, Response $response): Route
	{
		$params = array();

		try {
			$route  = $this->router->match($request->getMethod(), $request->getURI()->getPath());
			$target = $route->getHandler();
			$params = $route->getParams();

		} catch(HttpException $e) {

			switch ($e->getCode()) {
				case HttpException::HTTP_NOT_FOUND:
					$target = $response->withStatus(404);
					break;

				case HttpException::HTTP_METHOD_NOT_ALLOWED:
					$allowed = $this->router->getAllowedMethods($request->getURI()->getPath());
					$target  = $response->withStatus(405)->withHeader('Allow', implode(', ', $allowed));
					break;

				default:
					throw $e;
			}
		}

		return new Route($target, $params);
	}
}
