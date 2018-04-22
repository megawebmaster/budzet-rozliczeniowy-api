<?php
declare(strict_types=1);

namespace App\Security\Guard;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class Auth0Guard extends AbstractGuardAuthenticator
{
  /** @var Parser */
  private $parser;
  /** @var Sha256 */
  private $signer;
  /** @var FilesystemAdapter */
  private $cache;
  /** @var string */
  private $tenantUrl;

  public function __construct($tenantUrl)
  {
    $this->parser = new Parser();
    $this->signer = new Sha256();
    $this->cache = new FilesystemAdapter();
    $this->tenantUrl = $tenantUrl;
  }

  /**
   * Returns a response that directs the user to authenticate.
   * This is called when an anonymous request accesses a resource that
   * requires authentication. The job of this method is to return some
   * response that "helps" the user start into the authentication process.
   *
   * @param Request $request The request that resulted in an AuthenticationException
   * @param AuthenticationException $authException The exception that started the authentication process
   * @return Response
   */
  public function start(Request $request, AuthenticationException $authException = null)
  {
    return new JsonResponse(['error' => 'Authentication required'], 401);
  }

  /**
   * Does the authenticator support the given Request?
   * If this returns false, the authenticator will be skipped.
   *
   * @param Request $request
   * @return bool
   */
  public function supports(Request $request)
  {
    return $request->headers->has('Authorization');
  }

  /**
   * Get the authentication credentials from the request and return them
   * as any type (e.g. an associate array).
   *
   * @param Request $request
   * @return mixed Any non-null value
   * @throws \UnexpectedValueException If null is returned
   */
  public function getCredentials(Request $request)
  {
    $token = substr($request->headers->get('Authorization'), 7);

    return [
      'token' => $this->parser->parse($token),
    ];
  }

  /**
   * Return a UserInterface object based on the credentials.
   * The *credentials* are the return value from getCredentials()
   * You may throw an AuthenticationException if you wish. If you return
   * null, then a UsernameNotFoundException is thrown for you.
   *
   * @param mixed $credentials
   * @param UserProviderInterface $userProvider
   * @throws AuthenticationException
   * @return UserInterface|null
   */
  public function getUser($credentials, UserProviderInterface $userProvider)
  {
    /** @var Token $token */
    $token = $credentials['token'];
    $username = $token->getClaim('email');

    return $userProvider->loadUserByUsername($username, $token);
  }

  /**
   * Returns true if the credentials are valid.
   *
   * @param mixed $credentials
   * @param UserInterface $user
   * @return bool
   * @throws AuthenticationException
   */
  public function checkCredentials($credentials, UserInterface $user)
  {
    try
    {
      $key = $this->cache->getItem('auth0.jwks');
      if(!$key->isHit())
      {
        $key->set($this->retrieveJwks());
        $key->expiresAt(new \DateTime('tomorrow'));
        $this->cache->save($key);
      }

      return $this->validateToken($credentials['token'], $key->get());
    }
    catch(InvalidArgumentException $e)
    {
      return false;
    }
  }

  /**
   * Called when authentication executed, but failed (e.g. wrong username password).
   *
   * @param Request $request
   * @param AuthenticationException $exception
   * @return Response|null
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
  {
    return new JsonResponse(['error' => 'Authentication failure: '.$exception->getMessage()], 403);
  }

  /**
   * Called when authentication executed and was successful!
   *
   * @param Request $request
   * @param TokenInterface $token
   * @param string $providerKey The provider (i.e. firewall) key
   * @return Response|null
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
  {
    return null;
  }

  /**
   * Does this method support remember me cookies?
   *
   * @return bool
   */
  public function supportsRememberMe()
  {
    return false;
  }

  private function validateToken(Token $token, array $jwks)
  {
    $keyId = $token->getHeader('kid');
    $keys = array_filter($jwks, function ($key) use ($keyId){
      return $key->kid === $keyId;
    });

    if(empty($keys))
    {
      return false;
    }

    $key = new Key($this->convertCertificate($keys[0]->x5c[0]));

    return $token->verify($this->signer, $key);
  }

  private function convertCertificate($certificate)
  {
    return
      '-----BEGIN CERTIFICATE-----'.PHP_EOL.
      chunk_split($certificate, 64, PHP_EOL).
      '-----END CERTIFICATE-----'.PHP_EOL;
  }

  private function retrieveJwks()
  {
    $json = file_get_contents($this->tenantUrl.'/.well-known/jwks.json');
    $jwks = json_decode($json);

    return array_filter($jwks->keys, function ($key){
      return $key->use === 'sig' && $key->kty === 'RSA' && $key->alg === 'RS256' && $key->kid && $key->x5c;
    });
  }
}
