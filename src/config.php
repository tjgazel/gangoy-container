<?php

use Gangoy\Core\Container\Adapter\CallableResolver;
use Gangoy\Core\Container\Adapter\ControllerInvoker;
use function DI\autowire;
use DI\Container;
use function DI\create;
use function DI\get;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Container\ContainerInterface;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;

return [

    // Settings that can be customized by users
	'app.httpVersion' => '1.1',
	'app.responseChunkSize' => 4096,
	'app.outputBuffering' => 'append',
	'app.determineRouteBeforeAppMiddleware' => false,
	'app.displayErrorDetails' => false,
	'app.addContentLengthHeader' => true,
	'app.routerCacheFile' => false,

	'settings' => [
		'httpVersion' => get('app.httpVersion'),
		'responseChunkSize' => get('app.responseChunkSize'),
		'outputBuffering' => get('app.outputBuffering'),
		'determineRouteBeforeAppMiddleware' => get('app.determineRouteBeforeAppMiddleware'),
		'displayErrorDetails' => get('app.displayErrorDetails'),
		'addContentLengthHeader' => get('app.addContentLengthHeader'),
		'routerCacheFile' => get('app.routerCacheFile'),
	],

    // Default Slim services
	'router' => create(Slim\Router::class)
		->method('setContainer', get(Container::class))
		->method('setCacheFile', get('app.routerCacheFile')),
	Slim\Router::class => get('router'),
	'errorHandler' => create(Gangoy\Core\Container\Adapter\Error::class)
		->constructor(get('app.displayErrorDetails')),
	'phpErrorHandler' => create(Gangoy\Core\Container\Adapter\PhpError::class)
		->constructor(get('app.displayErrorDetails')),
	'notFoundHandler' => create(Slim\Handlers\NotFound::class),
	'notAllowedHandler' => create(Slim\Handlers\NotAllowed::class),
	'environment' => function () {
		return new Slim\Http\Environment($_SERVER);
	},
	'request' => function (ContainerInterface $c) {
		return Request::createFromEnvironment($c->get('environment'));
	},
	'response' => function (ContainerInterface $c) {
		$headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
		$response = new Response(200, $headers);
		return $response->withProtocolVersion($c->get('settings')['httpVersion']);
	},
	'foundHandler' => create(ControllerInvoker::class)
		->constructor(get('foundHandler.invoker')),
	'foundHandler.invoker' => function (ContainerInterface $c) {
		$resolvers = [
            // Inject parameters by name first
			new AssociativeArrayResolver,
            // Then inject services by type-hints for those that weren't resolved
			new TypeHintContainerResolver($c),
            // Then fall back on parameters default values for optional route parameters
			new DefaultValueResolver(),
		];
		return new Invoker(new ResolverChain($resolvers), $c);
	},

	'callableResolver' => autowire(CallableResolver::class),

];
