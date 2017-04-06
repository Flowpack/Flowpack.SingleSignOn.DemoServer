<?php
namespace Flowpack\SingleSignOn\DemoServer\Controller;

/*                                                                                       *
 * This script belongs to the Flow Framework package "Flowpack.SingleSignOn.DemoServer". *
 *                                                                                       */

use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * SSO demo server configuration controller
 *
 * @Flow\Scope("singleton")
 */
class ConfigurationController extends ActionController {

    /**
     * @Flow\Inject
     * @var \Flowpack\SingleSignOn\Server\Domain\Repository\SsoClientRepository
     */
    protected $ssoClientRepository;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Display configuration and registered clients
     */
    public function indexAction() {
        $ssoClients = $this->ssoClientRepository->findAll();
        $this->view->assign('ssoClients', $ssoClients);

        $serverSettings = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Flowpack.SingleSignOn.Server');
        $yaml = Yaml::dump($serverSettings);
        $this->view->assign('serverSettings', $yaml);
    }

}

