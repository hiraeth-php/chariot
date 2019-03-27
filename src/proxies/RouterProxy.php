<?php

namespace Hiraeth\Chariot;

use Hiraeth;
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
	 * @return mixed A matching target for the route
	 */
	public function match(Request $request, Response $response)
	{
		try {
			return $this->router->match($request->getMethod(), $request->getURI()->getPath());

		} catch(HttpException $e) {

			switch ($e->getCode()) {
				case HttpException::HTTP_NOT_FOUND:
					return $response->withStatus(404);

				case HttpException::HTTP_METHOD_NOT_ALLOWED:
					return $response
						->withStatus(405)
						->withHeader('Allow', implode(', ', $router->getAllowedMethods($path)));

				default:
					throw $e;
			}
		}
	}
}
