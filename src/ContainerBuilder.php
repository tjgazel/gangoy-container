<?php

namespace Gangoy\Core\Container;

final class ContainerBuilder
{
	/**
	 * @var \DI\ContainerBuilder
	 */
	private $containerBuilder;

	public function __construct()
	{
		$this->containerBuilder = new \DI\ContainerBuilder;
		$this->startDefinitions();
	}

	public function addDefinitions(array $definitions)
	{
		$this->containerBuilder->addDefinitions($definitions);
	}

	/**
	 * @return \DI\Container
	 * @throws \Exception
	 */
	public function build()
	{
		return $this->containerBuilder->build();
	}

	private function startDefinitions()
	{
		$slimAdapterConfig = require __DIR__ . '/config.php';
		$appConfig = require __DIR__ . '/../../config/app.php';

		$this->containerBuilder->addDefinitions($slimAdapterConfig);
		$this->containerBuilder->addDefinitions($appConfig);
		$this->containerBuilder->useAutowiring($appConfig['container.useAutowiring']);
		$this->containerBuilder->useAnnotations($appConfig['container.useAnnotations']);

		if ($appConfig['container.production']) {
			$this->containerBuilder->enableCompilation($appConfig['container.enableCompilation']);
			$this->containerBuilder->writeProxiesToFile(true, $appConfig['container.writeProxiesToFile']);
		}
	}
}
