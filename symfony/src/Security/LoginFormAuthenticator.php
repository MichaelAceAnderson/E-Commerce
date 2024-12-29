<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
	use TargetPathTrait;

	public const LOGIN_ROUTE = 'app_user_login';

	public function __construct(private UrlGeneratorInterface $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

	public function authenticate(Request $request): Passport
	{
		$email = $request->request->all()['login']['email'];

		$request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);
		return new Passport(
			new UserBadge($email),
			new PasswordCredentials($request->request->all()['login']['password'] ?? ''),
			[
				new CsrfTokenBadge('authenticate', $request->request->all()['login']['auth_token']),
				new RememberMeBadge(),
			]
		);
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
	{
		// If getTargetPath returns a URL, redirect the user to this URL
		// This URL is configured in the security.yaml file
		if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
			return new RedirectResponse($targetPath);
		}

		// Otherwise, redirect the user to their account page
		return new RedirectResponse($this->urlGenerator->generate('app_user_show'));
	}
	
	/**
	 * This method handles authentication errors by overriding the onAuthenticationFailure method of the parent class
	 * 
	 * @param Request $request The HTTP request
	 * @param AuthenticationException $exception The authentication exception
	 * 
	 */
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response    
	{
		// Determine the cause of the authentication error
		if ($exception instanceof InvalidCsrfTokenException) {
			$message = 'The CSRF token is invalid. Please try again.';
		} elseif ($exception instanceof BadCredentialsException) {
			$message = 'The provided credentials are incorrect.';
		} else {
			$message = 'An error occurred during authentication.';
		}

		// If we are in dev or local mode, display the error message
		if ($_SERVER['APP_ENV'] === 'dev' || $_SERVER['APP_ENV'] === 'local') {
			$message .= PHP_EOL. 'Original message: '. $exception->getMessage(). PHP_EOL
			.'Original exception class: '.get_class($exception);
		}
		
		// Create a custom authentication exception
		$exception = new CustomUserMessageAuthenticationException($message, $exception->getMessageData(), $exception->getCode(), $exception);

		if ($request->hasSession()) {
			$request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
		}

		$url = $this->getLoginUrl($request);

		return new RedirectResponse($url);
	}

	protected function getLoginUrl(Request $request): string
	{
		return $this->urlGenerator->generate(self::LOGIN_ROUTE);
	}
}
