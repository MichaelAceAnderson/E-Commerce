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
		// Si getTargetPath retourne une URL, on redirige l'utilisateur vers cette URL
		// Cette URL est configurée dans le fichier security.yaml
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

		// Sinon, on redirige l'utilisateur vers la page de son compte 
        return new RedirectResponse($this->urlGenerator->generate('app_user_show'));
    }
	
	/**
	 * Cette méthode permet de gérer les erreurs d'authentification en surchargeant la méthode onAuthenticationFailure de la classe parente
	 * 
	 * @param Request $request La requête HTTP
	 * @param AuthenticationException $exception L'exception d'authentification
	 * 
	 */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response	
    {
		// Déterminer la cause de l'erreur d'authentification
        if ($exception instanceof InvalidCsrfTokenException) {
			$message = 'Le jeton CSRF est invalide. Veuillez réessayer.';
        } elseif ($exception instanceof BadCredentialsException) {
			$message = 'Les informations d\'identification fournies sont incorrectes.';
        } else {
			$message = 'Une erreur est survenue lors de l\'authentification.';
        }

		// Si l'on est en mode dev ou local, on affiche le message d'erreur
		if ($_SERVER['APP_ENV'] === 'dev' || $_SERVER['APP_ENV'] === 'local') {
			$message .= PHP_EOL. 'Message original: '. $exception->getMessage(). PHP_EOL
			.'Classe de l\'exception originale : '.get_class($exception);
		}
		
		// Créer une exception d'authentification personnalisée
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
