<?php
namespace Flowpack\SingleSignOn\DemoServer\Controller;

/*                                                                                       *
 * This script belongs to the Flow Framework package "Flowpack.SingleSignOn.DemoServer". *
 *                                                                                       */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Session\Session;

/**
 * SSO demo server sessions controller
 *
 * @Flow\Scope("singleton")
 */
class SessionsController extends ActionController {

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Session\SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * Display sessions
     *
     * @return void
     */
    public function indexAction() {
        $currentSession = $this->sessionManager->getCurrentSession();
        $this->view->assign('currentSession', $currentSession);

        $activeSessions = $this->sessionManager->getActiveSessions();
        $this->view->assign('activeSessions', $activeSessions);
    }

    /**
     * Destroy a session
     *
     * @param Session $session
     * @return void
     * @Flow\IgnoreValidation("$session")
     */
    public function destroyAction(Session $session) {
        $session->destroy('Through web interface');

        $this->addFlashMessage('Session destroyed');

        $this->redirect('index');
    }

    /**
     * Destroys all sessions
     *
     * @return void
     */
    public function destroyAllAction() {
        $sessions = $this->sessionManager->getActiveSessions();
        foreach ($sessions as $session) {
            $session->destroy('Through test service');
        }

        $this->addFlashMessage('All sessions destroyed');

        $this->redirect('index');
    }

}

