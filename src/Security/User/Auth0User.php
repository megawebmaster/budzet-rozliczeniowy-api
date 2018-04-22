<?php
declare(strict_types=1);

namespace App\Security\User;

use Lcobucci\JWT\Token;
use Symfony\Component\Security\Core\User\UserInterface;

class Auth0User implements UserInterface
{
  private $username;
  private $id;
  private $avatar;

  /**
   * @param $username string Username
   * @param Token $token
   */
  public function __construct($username, Token $token)
  {
    $this->username = $username;
    $this->id = $token->getClaim('sub');
    $this->avatar = $token->getClaim('picture');
  }

  public function getRoles(): array
  {
    return ['ROLE_USER'];
  }

  public function getPassword(): string
  {
    return '';
  }

  public function getSalt(): ?string
  {
    return null;
  }

  public function getUsername(): string
  {
    return $this->username;
  }

  public function eraseCredentials()
  {
  }

  /**
   * @return string User's unique Auth0 ID.
   */
  public function getId(): string
  {
    return $this->id;
  }

  /**
   * @return string Url to user's avatar.
   */
  public function getAvatar(): string
  {
    return $this->avatar;
  }
}
