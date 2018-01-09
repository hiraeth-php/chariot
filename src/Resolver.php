<?php

namespace Hiraeth\Chariot;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Awesomite\Chariot\InternalRouteInterface as Route;
use Awesomite\Chariot\RouterInterface as Router;
use Awesomite\Chariot\Exceptions\HttpException;
use Hiraeth;

/**
 *
 */
class Resolver
{
	/**
	 *
	 */
	protected $broker = NULL;


	/**
	 *
	 */
	protected $request = NULL;


	/**
	 *
	 */
	protected $response = NULL;


	/**
	 *
	 */
	protected $router = NULL;


	/**
	 *
	 */
	public function __construct(Hiraeth\Broker $broker)
	{
		$this->broker = $broker;
	}


	/**
	 *
	 */
	public function run(Router $router, Route $route, Request $request, Response $response)
	{
		$this->router   = $router;
		$this->request  = $request;
		$this->response = $response;
		$handler_object = NULL;
		$handler_action = NULL;
		$parameters     = array();
		$handler        = explode('::', $route->getHandler());

		foreach ($route->getParams() as $name => $value) {
			$this->request = $this->request->withAttribute($name, $value);
			$parameters[':' . $name] = $value;
		}

		if (class_exists($handler[0])) {
			$handler_object = $this->broker->make($handler[0], [':resolver' => $this]);
			$handler_action = $handler[1] ?? '__invoke';
		}

		if (!is_callable([$handler_object, $handler_action])) {
			throw new HttpException(
				sprintf('Could not resolve target action %s, not callable on URL: ', $route->getHandler()),
				$request->getURI()->getPath(),
				404
			);
		}

		return $this->broker->execute(
			[$handler_object, $handler_action],
			[':resolver' => $this] + $parameters
		);
	}


	/**
	 *
	 */
	public function getRequest()
	{
		return $this->request;
	}


	/**
	 *
	 */
	public function getResponse()
	{
		return $this->response;
	}


	/**
	 *
	 */
	public function getRouter()
	{
		return $this->router;
	}
}
