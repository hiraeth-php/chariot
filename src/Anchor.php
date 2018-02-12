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
	public function __invoke($target, array $params = array(), ParamProvider $provider = NULL)
	{
		if ($target instanceof RequestInterface) {
			$link   = $target->getUri()->getPath();
			$params = array_filter($params + $target->getQueryParams());

		} elseif (strpos($target, '/') !== FALSE) {
			$link   = $target;
			$params = array_filter($params);

		} else {
			$link = $this->router->LinkTo($target);

			foreach ($params as $key => $value) {
				if (is_numeric($key)) {
					$link->withParam($value, $provider->getRouteParameter($value));
					unset($params[$key]);
				}
			}
		}

		if ($params) {
			$link .= '?' . http_build_query($params);
		}

		return $link;
	}
}
