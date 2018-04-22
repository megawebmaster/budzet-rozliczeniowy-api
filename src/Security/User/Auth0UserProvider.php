<?php
declare(strict_types=1);

namespace App\Security\User;

use Lcobucci\JWT\Token;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Auth0UserProvider implements UserProviderInterface
{

  /**
   * Loads the user for the given username.
   * This method must throw UsernameNotFoundException if the user is not
   * found.
   *
   * @param string $username The username
   * @param Token|null $token
   * @return UserInterface
   */
  public function loadUserByUsername($username, Token $token = null)
  {
    if($token === null)
    {
      throw new UsernameNotFoundException();
    }

    return new Auth0User($username, $token);
  }

  /**
   * Refreshes the user.
   * It is up to the implementation to decide if the user data should be
   * totally reloaded (e.g. from the database), or if the UserInterface
   * object can just be merged into some internal array of users / identity
   * map.
   *
   * @param UserInterface $user
   * @return UserInterface
   * @throws UnsupportedUserException if the user is not supported
   */
  public function refreshUser(UserInterface $user)
  {
    return $user;
  }

  /**
   * Whether this provider supports the given user class.
   *
   * @param string $class
   * @return bool
   */
  public function supportsClass($class)
  {
    return Auth0User::class == $class;
  }
}
