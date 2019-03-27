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
	 *
	 */
	protected $app = NULL;


	/**
	 *
	 */
	protected $caching = TRUE;


	/**
	 *
	 */
	protected $cacheFile = NULL;


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
	 *
	 */
	public function __construct(Hiraeth\Application $app)
	{
		$this->app       = $app;
		$this->caching   = $app->getEnvironment('CACHING', TRUE);
		$this->cacheFile = $app->getConfig('chariot', 'cache_file', 'storage/cache/' . md5(__CLASS__));
	}


	/**
	 * Get the instance of the class for which the delegate operates.
	 *
	 * @access public
	 * @param Broker $broker The dependency injector instance
	 * @return object The instance of the class for which the delegate operates
	 */
	public function __invoke(Hiraeth\Broker $broker): object
	{
		if ($this->caching && $this->app->hasFile($this->cacheFile)) {
			$router = require $this->app->getFile($this->cacheFile)->getPathname();

		} else {
			$router = PatternRouter::createDefault();

			foreach ($this->app->getConfig('*', 'routing', NULL) as $collection => $config) {

				$routes   = $config['routes'] ?? [];
				$prefix   = $config['prefix'] ?? '/';
				$patterns = $config['patterns'] ?? [];

				foreach ($routes as $route) {
					$pattern = '/' . ltrim($prefix . $route['route'], '/');

					foreach ($route['methods'] as $method) {
						$router->addRoute($method, $pattern, $route['target']);
					}
				}

				foreach ($patterns as $hint => $pattern) {
					if (class_exists($pattern)) {
						$router->getPatterns()->addPattern($hint, $broker->make($pattern));
					} else {
						$router->getPatterns()->addPattern($hint, $pattern);
					}
				}
			}

			if ($this->caching) {
				file_put_contents(
					$this->app->getFile($this->cacheFile, TRUE)->getPathname(),
					sprintf('<?php return %s;', $router->exportToExecutable())
				);
			}

			// $router->addParamDecorator($broker->make('Hiraeth\Chariot\ParamDecorator'));
		}

		$broker->share($router);

		return $router;
	}
}
