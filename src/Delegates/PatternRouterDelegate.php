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
	protected $config = NULL;


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
	static public function getClass()
	{
		return 'Awesomite\Chariot\Pattern\PatternRouter';
	}


	/**
	 * Get the interfaces for which the delegate operates.
	 *
	 * @static
	 * @access public
	 * @return array A list of interfaces for which the delegate provides a class
	 */
	static public function getInterfaces()
	{
		return ['Awesomite\Chariot\RouterInterface'];
	}


	/**
	 *
	 */
	public function __construct(Hiraeth\Application $app, Hiraeth\Configuration $config)
	{
		$this->app       = $app;
		$this->config    = $config;
		$this->caching   = $app->getEnvironment('CACHING', TRUE);
		$this->cacheFile = $config->get('chariot', 'cache_file', NULL);

		if ($this->caching && !$this->cacheFile) {
			throw new RuntimeException(
				'Please specify "cache_file" in the chariot configuration.'
			);
		}
	}


	/**
	 * Get the instance of the class for which the delegate operates.
	 *
	 * @access public
	 * @param Broker $broker The dependency injector instance
	 * @return Object The instance of the class for which the delegate operates
	 */
	public function __invoke(Hiraeth\Broker $broker)
	{
		if ($this->caching && $this->app->hasFile($this->cacheFile)) {
			return require $this->app->getFile($this->cacheFile);

		} else {
			$router = PatternRouter::createDefault();

			foreach (array_keys($this->config->get('*', 'chariot', array())) as $config) {
				$group    = $this->config->get($config, 'chariot.group', NULL);
				$routes   = $this->config->get($config, 'chariot.routes', array());
				$patterns = $this->config->get($config, 'chariot.patterns', array());

				foreach ($patterns as $hint => $pattern) {
					if (class_exists($pattern)) {
						$router->getPatterns()->addPattern($hint, $broker->make($pattern));
					} else {
						$router->getPatterns()->addPattern($hint, $pattern);
					}
				}

				foreach ($routes as $route => $settings) {
					$route = rtrim($group, '/') . $route;

					if (!isset($settings['target'])) {
						throw new RuntimeException(sprintf(
							'Invalid configuration for route "%s", no target specified',
							$route
						));
					}

					$router->any($route, $settings['target'], $settings['params'] ?? array());
				}
			}

			if ($this->caching) {
				file_put_contents(
					$this->app->getFile($this->cacheFile, TRUE),
					sprintf('<?php return %s;', $router->exportToExecutable())
				);
			}
		}

		return $router;
	}
}
