<?php
namespace Flowpack\SingleSignOn\DemoServer\Controller\Test;

/*                                                                                       *
 * This script belongs to the Flow Framework package "Flowpack.SingleSignOn.DemoServer". *
 *                                                                                       */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * Session management for acceptance tests
 *
 * @Flow\Scope("singleton")
 */
class SessionController extends ActionController {

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Session\SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var string
     */
    protected $defaultViewObjectName = 'TYPO3\Flow\Mvc\View\JsonView';

    /**
     * @var array
     */
    protected $supportedMediaTypes = array('application/json');

    /**
     * Destroy all active sessions
     */
    public function destroyAllAction() {
        $sessions = $this->sessionManager->getActiveSessions();
        foreach ($sessions as $session) {
            $session->destroy('Through test service');
        }

        $this->view->assign('value', array('success' => true));
    }

}
