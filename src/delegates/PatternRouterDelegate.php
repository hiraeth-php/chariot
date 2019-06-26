<?php

namespace Hiraeth\Chariot;

use Hiraeth;
use RuntimeException;
use Awesomite\Chariot\Pattern\PatternRouter;

/**
 * Delegates are responsible for constructing dependencies for the dependency injector.
 */
class PatternRouterDelegate implements Hiraeth\Delegate
{
	/**
	 * Get the class for which the delegate operates.
	 *
	 * @static
	 * @access public
	 * @return string The class for which the delegate operates
	 */
	static public function getClass(): string
	{
		return PatternRouter::class;
	}


	/**
	 * Get the instance of the class for which the delegate operates.
	 *
	 * @access public
	 * @param Hiraeth\Application $app The application instance for which the delegate operates
	 * @return object The instance of the class for which the delegate operates
	 */
	public function __invoke(Hiraeth\Application $app): object
	{
		$caching    = $app->getEnvironment('CACHING', TRUE);
		$cache_file = $app->getConfig('chariot', 'cache_file', 'storage/cache/' . md5(__CLASS__));

		if ($caching && $app->hasFile($cache_file)) {
			$router = require $app->getFile($cache_file)->getPathname();

		} else {
			$router = PatternRouter::createDefault();
			$base   = $app->getEnvironment('BASE_PATH', '/');

			foreach ($app->getConfig('*', 'routing', NULL) as $collection => $config) {
				$routes   = $config['routes'] ?? [];
				$prefix   = $config['prefix'] ?? '/';

				foreach ($routes as $route) {
					$pattern = preg_replace('#[/]+#', '/', sprintf(
						'%s/%s/%s',
						$base,
						$prefix,
						$route['route']
					));

					foreach ($route['methods'] as $method) {
						$router->addRoute($method, $pattern, $route['target']);
					}
				}
			}

			foreach ($app->getConfig('*', 'chariot', NULL) as $collection => $config) {
				$patterns = $config['patterns'] ?? [];

				foreach ($patterns as $hint => $pattern) {
					if (class_exists($pattern)) {
						$router->getPatterns()->addPattern($hint, $app->get($pattern));
					} else {
						$router->getPatterns()->addPattern($hint, $pattern);
					}
				}
			}

			if ($caching) {
				file_put_contents(
					$app->getFile($cache_file, TRUE)->getPathname(),
					sprintf('<?php return %s;', $router->exportToExecutable())
				);
			}

			// $router->addParamDecorator($app->get('Hiraeth\Chariot\ParamDecorator'));
		}

		return $app->share($router);
	}
}
