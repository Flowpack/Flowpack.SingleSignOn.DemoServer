<?php
namespace Acme\DemoServer\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Acme.DemoServer".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Login controller for local authentication
 *
 * This controller will be called if no account was authenticated
 * on the SSO server.
 *
 * @Flow\Scope("singleton")
 */
class LoginController extends \TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController {

	/**
	 * Render a login form
	 */
	public function indexAction() {

	}

	/**
	 * Is called if authentication was successful
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
		if ($originalRequest !== NULL) {
			$this->redirectToRequest($originalRequest);
		}

		$this->addFlashMessage('No original SSO request present. Account authenticated on server.', 'Authentication successful', \TYPO3\Flow\Error\Message::SEVERITY_OK);
		$this->redirect('index', 'Standard');
	}

	/**
	 * Logout
	 *
	 * @return void
	 */
	public function logoutAction() {
		parent::logoutAction();

		$this->addFlashMessage('You have been logged out');
		$this->redirect('index', 'Standard');
	}

}
?>