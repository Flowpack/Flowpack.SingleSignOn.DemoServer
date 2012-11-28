<?php
namespace Acme\DemoServer\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Acme.DemoServer".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Setup command controller
 *
 * @Flow\Scope("singleton")
 */
class SetupCommandController extends \TYPO3\Flow\Cli\CommandController {

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
	 * @var \TYPO3\SingleSignOn\Server\Domain\Repository\SsoClientRepository
	 */
	protected $ssoClientRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Server\Domain\Repository\AccessTokenRepository
	 */
	protected $accessTokenRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \Acme\DemoServer\Domain\Repository\UserRepository
	 */
	protected $userRepository;

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
	 * Sets up a demo server installation with fixture data
	 *
	 * Overwrites existing data in the database.
	 *
	 * DO NOT USE IT FOR PRODUCTION!
	 */
	public function setupCommand() {
		$privateKeyString = \TYPO3\Flow\Utility\Files::getFileContents('resource://Acme.DemoServer/Private/Fixtures/DemoServer.key', FILE_TEXT);
		if ($privateKeyString === FALSE) {
			$this->outputLine('Could not open DemoServer.key, aborting.');
			return;
		}
		$serverKeyPairUuid = $this->rsaWalletService->registerKeyPairFromPrivateKeyString($privateKeyString);
		$this->outputLine('Registered demo server key pair');

		$globalSettings = $this->yamlSource->load(FLOW_PATH_CONFIGURATION . '/Settings');
		$globalSettings['TYPO3']['SingleSignOn']['Server']['server']['keyPairUuid'] = $serverKeyPairUuid;
		$this->yamlSource->save(FLOW_PATH_CONFIGURATION . '/Settings', $globalSettings);
		$this->outputLine('Updated settings');

		$clientPublicKeyString = \TYPO3\Flow\Utility\Files::getFileContents('resource://Acme.DemoServer/Private/Fixtures/DemoClient.pub', FILE_TEXT);
		if ($clientPublicKeyString === FALSE) {
			$this->outputLine('Could not open DemoClient.pub, aborting.');
			return;
		}
		$clientPublicKeyUuid = $this->rsaWalletService->registerPublicKeyFromString($clientPublicKeyString);
		$this->outputLine('Registered demo client key pair');

		$this->accessTokenRepository->removeAll();
		$this->ssoClientRepository->removeAll();
		$this->accountRepository->removeAll();
		$this->userRepository->removeAll();
			// Persist removal, because otherwise primary key constraints fail
		$this->persistenceManager->persistAll();

		$ssoClient = new \TYPO3\SingleSignOn\Server\Domain\Model\SsoClient();
		$ssoClient->setIdentifier('demoinstance');
		$ssoClient->setPublicKey($clientPublicKeyUuid);
		$this->ssoClientRepository->add($ssoClient);
		$this->outputLine('Created demo client with identifier "' . $ssoClient->getIdentifier() . '"');

		$this->addUserCommand('admin', 'password', 'Administrator');
	}

	/**
	 * Add a user and account with DefaultProvider
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $roles
	 * @return void
	 */
	public function addUserCommand($username, $password, $roles) {
		$roleIdentifiers = \TYPO3\Flow\Utility\Arrays::trimExplode(',', $roles);
		$user = new \Acme\DemoServer\Domain\Model\User();
		$user->setUsername($username);
		$user->setPassword($password);
		$user->setRole($roleIdentifiers[0]);
		$this->userRepository->add($user);
		$this->outputLine('Created user and account with identifier "' . $username . '"');
	}

}
?>