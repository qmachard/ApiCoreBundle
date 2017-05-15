<?php
/**
 * Created by PhpStorm.
 * User: quentinmachard
 * Date: 21/07/2016
 * Time: 16:22
 */

namespace QuentinMachard\ApiCoreBundle\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException {
	private $apiProblem;

	/**
	 * ApiProblemException constructor.
	 * @param ApiProblem $apiProblem
	 * @param \Exception|NULL $previous
	 * @param array $headers
	 * @param int $code
	 */
	public function __construct(ApiProblem $apiProblem, \Exception $previous = null, array $headers = array(), $code = 0) {
		$this->apiProblem = $apiProblem;
		$statusCode = $apiProblem->getStatusCode();
		$message = $apiProblem->getTitle();
		parent::__construct($statusCode, $message, $previous, $headers, $code);
	}

	/**
	 * @return ApiProblem
	 */
	public function getApiProblem() {
		return $this->apiProblem;
	}
}