<?php

namespace Hiraeth\Chariot;

/**
 *
 */
trait EntityParamProvider
{
	/**
	 *
	 */
	public function getRouteParameter($name)
	{
		return $this->{ 'get' . ucfirst($name) }();
	}
}
