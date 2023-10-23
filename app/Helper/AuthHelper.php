<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\AuthorizationInterface;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Session\Session;

/**
 * @class AuthHelper
 * @package Platine\App\Helper
 */
class AuthHelper
{
    /**
     * The authentication instance
     * @var AuthenticationInterface
     */
    protected AuthenticationInterface $authentication;

    /**
     * The authorization instance
     * @var AuthorizationInterface
     */
    protected AuthorizationInterface $authorization;

    /**
     * The UserRepository instance
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * The session instance
     * @var Session
     */
    protected Session $session;


    /**
     * Create new instance
     * @param AuthenticationInterface $authentication
     * @param AuthorizationInterface $authorization
     * @param UserRepository $userRepository
     * @param Session $session
     */
    public function __construct(
        AuthenticationInterface $authentication,
        AuthorizationInterface $authorization,
        UserRepository $userRepository,
        Session $session
    ) {
        $this->authentication = $authentication;
        $this->authorization = $authorization;
        $this->userRepository = $userRepository;
        $this->session = $session;
    }

    /**
     *
     * @return bool
     */
    public function isLogged(): bool
    {
        return $this->authentication->isLogged();
    }

    /**
     * Return the current user id
     * @return int
     */
    public function getUserId(): int
    {
        return $this->session->get('user.id', 1);
    }


    /**
     * Return the current user enterprise id
     * @return int
     */
    public function getEnterpiseId(): int
    {
        return $this->session->get('enterprise_id', 1);
    }

    /**
     * Return the user instance
     * @return User
     */
    public function getUser(): User
    {
        $id = $this->getUserId();

        $user = $this->userRepository->with('person')->find($id);
        if ($user === null) {
            throw new AccountNotFoundException('Can not find the user using the session data');
        }

        return $user;
    }
}
