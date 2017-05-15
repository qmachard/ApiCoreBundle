<?php
/**
 * Created by PhpStorm.
 * User: quentinmachard
 * Date: 03/08/2016
 * Time: 16:36
 */

namespace Qwentyn\ApiCoreBundle\Api;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service api.response
 * Class ApiResponse
 * @package Paddix\CoreBundle\Api
 */
class ApiResponse {
	/**
	 * @var Serializer
	 */
	private $serializer;

	/**
	 * ApiResponse constructor.
	 * @param Serializer $serializer
	 */
	public function __construct(Serializer $serializer) {
		$this->serializer = $serializer;
	}

	/**
	 * Serialize datas to format
	 *
	 * @param mixed $data
	 * @param string $format
	 * @param array|string $serializerGroups
	 * @return mixed|string
	 */
	public function serialize($data, $format = 'json', $serializerGroups = array()) {
		$context = new SerializationContext();
		$context->setSerializeNull(true);
		$context->enableMaxDepthChecks();

		// Set visible attributes by group
		if(!is_array($serializerGroups)) {
			$serializerGroups = array($serializerGroups);
		}
		$context->setGroups(array_merge(array('Default'), $serializerGroups));

		return $this->serializer->serialize($data, $format, $context);
	}

	/**
	 * Serialize datas to format
	 *
	 * @param mixed $data
	 * @param array|string $serializerGroups
	 * @return mixed|string
	 */
	public function toArray($data, $serializerGroups = array()) {
		$context = new SerializationContext();
		$context->setSerializeNull(true);

		// Set visible attributes by group
		if(!is_array($serializerGroups)) {
			$serializerGroups = array($serializerGroups);
		}
		$context->setGroups(array_merge(array('Default'), $serializerGroups));

		return $this->serializer->toArray($data, $context);
	}

	/**
	 * Create a Symfony Reponse
	 *
	 * @param $data
	 * @param int $statusCode
	 * @param array|string $serializerGroups
	 * @return Response
	 */
	public function createApiResponse($data, $statusCode = 200, $serializerGroups = array()) {
		$json = $this->serialize($data, 'json', $serializerGroups);
		return new Response($json, $statusCode, array(
			'Content-Type' => 'application/json',
			'Content-Length' => strlen($json),
		));
	}

	/**
	 * Create a ApiProblem Reponse
	 *
	 * @param ApiProblem $apiProblem
	 * @return JsonResponse
	 */
	public function createApiProblemResponse(ApiProblem $apiProblem) {
		$data = $apiProblem->toArray();
		// making type a URL, to a temporarily fake page
		if ($data['type'] != 'about:blank') {
			$data['type'] = '/docs/errors#'.$data['type'];
		}
		$response = new JsonResponse(
			$data,
			$apiProblem->getStatusCode()
		);
		$response->headers->set('Content-Type', 'application/problem+json');
		return $response;
	}
}