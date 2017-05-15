<?php
/**
 * Created by PhpStorm.
 * User: quentinmachard
 * Date: 21/07/2016
 * Time: 16:27
 */

namespace QuentinMachard\ApiCoreBundle\EventListener;

use Monolog\Logger;
use QuentinMachard\ApiCoreBundle\Api\ApiProblemException;
use QuentinMachard\ApiCoreBundle\Api\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface {

	/**
	 * @var bool
	 */
	private $isDev;

	/**
	 * @var ApiResponse
	 */
	private $apiResponse;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * ApiExceptionSubscriber constructor.
	 * @param KernelInterface $kernel
	 * @param ApiResponse $apiResponse
	 * @param Logger $logger
	 */
	public function __construct(KernelInterface $kernel, ApiResponse $apiResponse, Logger $logger) {
		$this->isDev = ($kernel->getEnvironment() == "dev" || $kernel->getEnvironment() == "test") ? true : false;
		$this->apiResponse = $apiResponse;
		$this->logger = $logger;
	}

	/**
	 * Callback when KernelEvents::EXCEPTION is triggered
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event) {
		$e = $event->getException();

		if(!$e instanceof ApiProblemException) {
			return;
		}

		$apiProblem = $e->getApiProblem();

		if($this->isDev) {
			$apiProblem->set('file', $e->getFile());
			$apiProblem->set('line', $e->getLine());
			$apiProblem->set('trace', $e->getTrace());
		}

		// Log error
		$this->logger->critical($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
		$this->logger->debug($e->getTraceAsString());

		$response = $this->apiResponse->createApiProblemResponse($apiProblem);

		$event->setResponse($response);
	}

	/**
	 * Implementation of EventSubscriberInterface
	 * @return array
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::EXCEPTION => 'onKernelException'
		);
	}
}