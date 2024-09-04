<?php

namespace Andre1502\NetUtilities\Traits;

use Andre1502\NetUtilities\Exceptions\APIErrorException;
use Andre1502\NetUtilities\Traits\Config;
use DateTimeImmutable;
use Exception;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Symfony\Component\HttpFoundation\Response;

trait JWT
{
  use Config;

  private string $logChannel;
  private array $jwtAuth;
  private InMemory $jwtSecret;
  private JwtFacade $jwtFacade;
  private Sha256 $hmacSha256;
  private SignedWith $signedWith;
  private Parser $parser;

  /**
   * @date 2024/08/20
   * @author Andre Lukito
   * @return
   */
  public function init()
  {
    $this->logChannel = $this->getLogChannel();
    $this->jwtAuth = $this->getJwtAuth();

    if ((!isset($this->jwtSecret)) && (!empty($this->jwtAuth["default"]["secret"]))) {
      $this->jwtSecret = InMemory::plainText($this->jwtAuth["default"]["secret"]);
    }

    if (!isset($this->jwtFacade)) {
      $this->jwtFacade = new JwtFacade();
    }

    if (!isset($this->hmacSha256)) {
      $this->hmacSha256 = new Sha256();
    }

    if ((!isset($this->signedWith)) && (isset($this->jwtSecret))) {
      $this->signedWith = new SignedWith($this->hmacSha256, $this->jwtSecret);
    }

    if (!isset($this->parser)) {
      $this->parser = new Parser(new JoseEncoder());
    }
  }

  /**
   * @date 2024/08/20
   * @author Andre Lukito
   * @param Builder $builder
   * @param DateTimeImmutable $issuedAt
   * @param int $userId
   * @param ?array $data
   * @return Builder
   */
  private function tokenBuilder(Builder $builder, DateTimeImmutable $issuedAt, int $userId, ?array $data = null) : Builder
  {
    $expiresAt = $issuedAt->getTimestamp() + ($this->jwtAuth["default"]["ttl"] * 60);
    $expiresAtDTI = (new DateTimeImmutable())->setTimestamp($expiresAt);

    $tokenBuilder = $builder
      // Configures the issuer (iss claim)
      ->issuedBy(url()->current())
      // Configures the subject of the token (sub claim)
      ->relatedTo($userId)
      // Configures the time that the token can be used (nbf claim)
      ->canOnlyBeUsedAfter($issuedAt->modify("-1 hour"))
      // Configures the expiration time of the token (exp claim)
      ->expiresAt($expiresAtDTI)
      // Configures a new claim, called "uid"
      ->withClaim("uid", $userId);

    if (!empty($data)) {
      foreach ($data as $key => &$value) {
        $tokenBuilder = $tokenBuilder->withClaim($key, $value);
      }
    }

    return $tokenBuilder;
  }

  /**
   * @date 2024/08/20
   * @author Andre Lukito
   * @param int userId
   * @param ?array data
   * @return string
   */
  public function issueToken(int $userId, ?array $data = null) : string
  {
    $this->init();

    try {
      $token = $this->jwtFacade->issue($this->hmacSha256, $this->jwtSecret,
        fn (Builder $builder, DateTimeImmutable $issuedAt) : Builder => $this->tokenBuilder($builder, $issuedAt, $userId, $data));

      return $token->toString();
    } catch (Exception $ex) {
      throw new APIErrorException(Response::HTTP_BAD_REQUEST, "{$this->configName}::error.FAILED_ISSUE_JWT", ["raw_ex" => $ex]);
    }
  }

  /**
   * @date 2024/08/20
   * @author Andre Lukito
   * @param string $token
   * @return UnencryptedToken
   */
  public function validateToken(string $token, ?string $secret = null) : UnencryptedToken
  {
    $this->init();

    try {
      $signedWith = $this->signedWith ?? null;

      if (!empty($secret)) {
        $signedWith = new SignedWith($this->hmacSha256, InMemory::plainText($secret));
      }

      return $this->jwtFacade->parse($token, $signedWith, new StrictValidAt(SystemClock::fromSystemTimezone()));
    } catch (RequiredConstraintsViolated $ex) {
      if ($ex->violations()[0]->constraint === SignedWith::class) {
        throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.INVALID_TOKEN_SIGNATURE", ["raw_ex" => $ex]);
      }

      if ($ex->violations()[0]->constraint === StrictValidAt::class) {
        throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.TOKEN_EXPIRED", ["raw_ex" => $ex]);
      }

      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.FAILED_PARSE_JWT", ["raw_ex" => $ex]);
    } catch (Exception $ex) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.FAILED_PARSE_JWT", ["raw_ex" => $ex]);
    }
  }

  /**
   * @date 2024/08/19
   * @author Andre Lukito
   * @param string $token
   * @return UnencryptedToken
   */
  public function parseToken(string $token) : UnencryptedToken
  {
    $this->init();

    try {
      return $this->parser->parse($token);
    } catch (Exception $ex) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.FAILED_PARSE_JWT", ["raw_ex" => $ex]);
    }
  }

  /**
   * @date 2024/08/20
   * @author Andre Lukito
   * @param UnencryptedToken $token
   * @return mixed
   */
  public function getClaimByKey(UnencryptedToken $token, string $claimKey) : mixed
  {
    $this->init();

    try {
      return $token->claims()->get($claimKey);
    } catch (Exception $ex) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.JWT_CLAIM_NOT_FOUND", ["raw_ex" => $ex]);
    }
  }
}
