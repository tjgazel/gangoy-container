<?php

namespace Gangoy\Core\Container\Adapter;

use Slim\Interfaces\CallableResolverInterface;

/**
 * Resolve middleware and route callables using PHP-DI.
 */
class CallableResolver implements CallableResolverInterface
{
    /**
     * @var \Invoker\CallableResolver
     */
    private $callableResolver;

    public function __construct(\Invoker\CallableResolver $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

	/**
	 * {@inheritdoc}
	 * @throws \Invoker\Exception\NotCallableException
	 */
    public function resolve($toResolve)
    {
        return $this->callableResolver->resolve($toResolve);
    }
}
