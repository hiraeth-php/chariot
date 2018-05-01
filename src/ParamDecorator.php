<?php

namespace Hiraeth\Chariot;

use Awesomite\Chariot\ParamDecorators\ParamDecoratorInterface;
use Awesomite\Chariot\ParamDecorators\ContextInterface;

class ParamDecorator implements ParamDecoratorInterface
{
	/**
	 *
	 */
	public function decorate(ContextInterface $context)
	{
		if (!isset($context->getParams()['__provider__'])) {
			return;
		}

		$provider = $context->getParams()['__provider__'];

		foreach ($context->getRequiredParams() as $param) {
			$context->setParam($param, $provider->getRouteParameter($param));
		}

		$context->removeParam('__provider__');
	}
}
