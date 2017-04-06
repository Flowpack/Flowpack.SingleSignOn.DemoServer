<?php
namespace Flowpack\SingleSignOn\DemoServer\Domain\Model;

/*                                                                                       *
 * This script belongs to the Flow Framework package "Flowpack.SingleSignOn.DemoServer". *
 *                                                                                       */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Party\Domain\Model\AbstractParty;

/**
 * User domain model
 *
 * @Flow\Entity
 */
class User extends AbstractParty {

    /**
     * @var string
     */
    protected $firstname = '';

    /**
     * @var string
     */
    protected $lastname = '';

    /**
     * @var string
     */
    protected $company = '';

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Security\Cryptography\HashService
     */
    protected $hashService;

    /**
     * @param string $username
     * @return void
     */
    public function setUsername($username) {
        $account = $this->getPrimaryAccount();
        $account->setAccountIdentifier($username);
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->getPrimaryAccount()->getAccountIdentifier();
    }

    /**
     * @param string $company
     */
    public function setCompany($company) {
        $this->company = $company;
    }

    /**
     * @return \TYPO3\Flow\Security\Policy\Role
     */
    public function getRole() {
        return current($this->getPrimaryAccount()->getRoles());
    }

    /**
     * @return string
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $account = $this->getPrimaryAccount();
        $account->setCredentialsSource($this->hashService->hashPassword($password));
    }

    /**
     * @return \TYPO3\Flow\Security\Account
     */
    public function getPrimaryAccount() {
        if (count($this->accounts) > 0) {
            return $this->accounts->first();
        }
        return null;
    }

}

