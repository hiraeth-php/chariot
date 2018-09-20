<?php

namespace Hiraeth\Chariot;

use Awesomite\Chariot\Pattern\PatternRouter;
use Psr\Http\Message\RequestInterface;

class Anchor
{
	/**
	 *
	 */
	public function __construct(PatternRouter $router)
	{
		$this->router = $router;
	}


	/**
	 *
	 */
	public function __invoke($target, ParamProvider $provider = NULL, array $query = array())
	{
		if ($target instanceof RequestInterface) {
			$link  = $target->getUri()->getPath();
			$query = $query + $target->getQueryquery();

		} elseif (strpos($target, '/') === FALSE) {
			$link = $this->router->LinkTo($target);

			if ($provider) {
				$link->withParam('__provider__', $provider);
			}

		} else {
			$link = $target;
		}

		$query = array_filter($query, function($value) {
			return $value !== NULL;
		});

		if ($query) {
			$link .= '?' . http_build_query($query);
		}

		return $link;
	}


	/**
	 *
	 */
	public function make($target, ParamProvider $provider = NULL, array $query = array())
	{
		return $this->__invoke($target, $provider, $query);
	}
}
