<?php
namespace Flowpack\SingleSignOn\DemoServer\Controller;

/*                                                                                       *
 * This script belongs to the Flow Framework package "Flowpack.SingleSignOn.DemoServer". *
 *                                                                                       */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * SSO demo server standard controller
 *
 * @Flow\Scope("singleton")
 */
class StandardController extends ActionController {

    /**
     * Display a welcome screen
     *
     * @return void
     */
    public function indexAction() {
    }

}

