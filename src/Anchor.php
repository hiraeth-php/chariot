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
			$link   = $target->getUri()->getPath();
			$query = array_filter($query + $target->getQueryquery());

		} elseif (strpos($target, '/') !== FALSE) {
			$link   = $target;
			$query = array_filter($query);

		} else {
			$link = $this->router->LinkTo($target);

			if ($provider) {
				$link->withParam('__provider__', $provider);
			}
		}

		if ($query) {
			$link .= '?' . http_build_query($query);
		}

		return $link;
	}
}
