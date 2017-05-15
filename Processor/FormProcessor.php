<?php
/**
 * FormProcessor.php
 * @author Quentin Machard <quentin.machard@gmail.com>
 */

namespace Qwentyn\ApiCoreBundle\Processor;


use Qwentyn\ApiCoreBundle\Api\ApiProblem;
use Qwentyn\ApiCoreBundle\Api\ApiProblemException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FormProcessor {
	/**
	 * @param Request $request
	 * @param FormInterface $form
	 * @param bool $clearMissing
	 * @param bool $required
	 */
	public function processForm(Request $request, FormInterface $form, $clearMissing = false, $required = true) {
		$body = $this->parseBody($request, $required);
		$this->processFormData($body, $form, $clearMissing);
	}

	/**
	 * @param array $data
	 * @param FormInterface $form
	 * @param bool $clearMissing
	 */
	public function processFormData($data, FormInterface $form, $clearMissing = false) {
		$form->submit($data, $clearMissing);

		if(!$form->isValid()) {
			$this->throwApiProblemValidationException($form);
		}
	}

	/**
	 * Parse body from request in JSON
	 *
	 * @param Request $request
	 * @param bool $required
	 *
	 * @return mixed
	 */
	public function parseBody(Request $request, $required = true) {
		$body = json_decode($request->getContent(), true);

		if ($required && $body === null) {
			$apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);
			throw new ApiProblemException($apiProblem);
		}

		return $body;
	}

	/**
	 * @param FormInterface $form
	 * @return array
	 */
	public function getErrorsFromForm(FormInterface $form) {
		$errors = array();
		foreach ($form->getErrors() as $error) {
			$errors[] = $error->getMessage();
		}
		foreach ($form->all() as $childForm) {
			if ($childForm instanceof FormInterface) {
				if ($childErrors = $this->getErrorsFromForm($childForm)) {
					$errors[$childForm->getName()] = $childErrors;
				}
			}
		}
		return $errors;
	}

	/**
	 * @param FormInterface $form
	 */
	public function throwApiProblemValidationException(FormInterface $form) {
		$errors = $this->getErrorsFromForm($form);
		$this->throwApiProblemErrorsValidationException($errors);
	}

	/**
	 * @param array $errors
	 * @throws ApiProblemException
	 */
	public function throwApiProblemErrorsValidationException($errors) {
		$apiProblem = new ApiProblem(400, ApiProblem::TYPE_VALIDATION_ERROR);
		$apiProblem->set('errors', $errors);
		throw new ApiProblemException($apiProblem);
	}
}