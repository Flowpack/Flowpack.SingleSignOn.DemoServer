<?php
namespace Flowpack\SingleSignOn\DemoServer\Command;

/*                                                                                       *
 * This script belongs to the Flow Framework package "Flowpack.SingleSignOn.DemoServer". *
 *                                                                                       */

use Flowpack\SingleSignOn\DemoServer\Domain\Model\User;
use Flowpack\SingleSignOn\Server\Domain\Model\SsoClient;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Flow\Utility\Files;

/**
 * Command controller for setting up a demo server
 *
 * @Flow\Scope("singleton")
 */
class DemoCommandController extends CommandController {

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
     */
    protected $rsaWalletService;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Configuration\Source\YamlSource
     */
    protected $yamlSource;

    /**
     * @Flow\Inject
     * @var \Flowpack\SingleSignOn\Server\Domain\Repository\SsoClientRepository
     */
    protected $ssoClientRepository;

    /**
     * @Flow\Inject
     * @var \Flowpack\SingleSignOn\Server\Domain\Repository\AccessTokenRepository
     */
    protected $accessTokenRepository;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Security\AccountRepository
     */
    protected $accountRepository;

    /**
     * @Flow\Inject
     * @var \Flowpack\SingleSignOn\DemoServer\Domain\Repository\UserRepository
     */
    protected $userRepository;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Security\Policy\PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Security\AccountFactory
     */
    protected $accountFactory;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     */
    public function injectSettings($settings) {
        $this->settings = $settings;
    }

    /**
     * Set up the SSO demo server
     *
     * This commands sets a up a demo server with some initial fixture data. It
     * overwrites existing data in the database.
     *
     * ONLY FOR DEMO PURPOSES - DO NOT USE IN PRODUCTION!
     *
     * @return void
     */
    public function setupCommand() {
        $privateKeyString = Files::getFileContents('resource://Flowpack.SingleSignOn.DemoServer/Private/Fixtures/DemoServer.key', FILE_TEXT);
        if ($privateKeyString === false) {
            $this->outputLine('Could not open resource://Flowpack.SingleSignOn.DemoServer/Private/Fixtures/DemoServer.key, exiting.');
            $this->quit(1);
        }

        if (!isset($this->settings['clients']) || !is_array($this->settings['clients'])) {
            $this->outputLine('Missing "Flowpack.SingleSignOn.DemoServer.clients" settings, exiting.');
            $this->quit(2);
        }

        $serverKeyPairFingerprint = $this->rsaWalletService->registerKeyPairFromPrivateKeyString($privateKeyString);
        $this->outputLine('Registered demo server key pair (fingerprint: %s)', array($serverKeyPairFingerprint));

        $globalSettings = $this->yamlSource->load(FLOW_PATH_CONFIGURATION . '/Settings');
        $globalSettings['Flowpack']['SingleSignOn']['Server']['server']['keyPairFingerprint'] = $serverKeyPairFingerprint;
        $this->yamlSource->save(FLOW_PATH_CONFIGURATION . '/Settings', $globalSettings);
        $this->outputLine('Wrote settings to file %s', array(FLOW_PATH_CONFIGURATION . '/Settings.yaml'));

        $this->accessTokenRepository->removeAll();
        $this->ssoClientRepository->removeAll();
        $this->accountRepository->removeAll();
        $this->userRepository->removeAll();

            // Persist the removal, because otherwise primary key constraints fail
        $this->persistenceManager->persistAll();

        foreach ($this->settings['clients'] as $clientConfiguration) {
            $ssoClient = new SsoClient();
            $ssoClient->setServiceBaseUri($clientConfiguration['serviceBaseUri']);

            if (isset($clientConfiguration['publicKeyFilename'])) {
                $clientPublicKeyFilename = $clientConfiguration['publicKeyFilename'];
            } else {
                $clientPublicKeyFilename = 'resource://Flowpack.SingleSignOn.DemoServer/Private/Fixtures/DemoClient.pub';
            }
            $clientPublicKeyString = Files::getFileContents($clientPublicKeyFilename, FILE_TEXT);
            if ($clientPublicKeyString === false) {
                $this->outputLine('Could not open %s, exiting.', array($clientPublicKeyFilename));
                $this->quit(3);
            }
            $clientPublicKeyFingerprint = $this->rsaWalletService->registerPublicKeyFromString($clientPublicKeyString);

            $ssoClient->setPublicKey($clientPublicKeyFingerprint);
            $this->ssoClientRepository->add($ssoClient);
            $this->outputLine('Created demo client "%s"', array($ssoClient->getServiceBaseUri()));
        }

        $this->createUserCommand('admin', 'password', 'Flowpack.SingleSignOn.DemoInstance:Administrator', 'Joe', 'Bloggs');
        $this->createUserCommand('user1', 'password', 'Flowpack.SingleSignOn.DemoInstance:User', 'Michael', 'Potter');
        $this->createUserCommand('user2', 'password', 'Flowpack.SingleSignOn.DemoInstance:User', 'Jamie', 'Morgan');
    }

    /**
     * Add a demo user
     *
     * This command adds a demo user and account configured for the DefaultProvider
     *
     * ONLY FOR DEMO PURPOSES - DO NOT USE IN PRODUCTION!
     *
     * @param string $username User name
     * @param string $password The user's password
     * @param string $role One or more roles to add (comma separated), for example Flowpack.SignleSignOn.DemoInstance:Administrator
     * @param string $firstname The user's first name
     * @param string $lastname The user's last name
     * @return void
     */
    public function createUserCommand($username, $password, $role, $firstname = '', $lastname = '') {
        $roleIdentifiers = Arrays::trimExplode(',', $role);

        $user = new User();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);

        $account = $this->accountFactory->createAccountWithPassword($username, $password);
        $user->addAccount($account);

        foreach ($roleIdentifiers as $roleIdentifier) {
            try {
                $role = $this->policyService->getRole($roleIdentifier);
            } catch (\TYPO3\Flow\Security\Exception\NoSuchRoleException $e) {
                $this->outputLine('Role "%s" does not exist, creating it.', array($roleIdentifier));
                $role = $this->policyService->createRole($roleIdentifier);
            }
            $account->addRole($role);
        }

        $this->userRepository->add($user);
        $this->outputLine('Created demo user and account with identifier "%s"', array($username));
    }

}
