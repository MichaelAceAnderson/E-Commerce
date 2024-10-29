<?php

namespace App\Controller;

use App\Form\LoginType;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\UserEditType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/user', name: 'app_user_')]
class UserController extends AbstractEntityController
{
	/**
	 * Create the entity controller
	 * 
	 * @param EntityManagerInterface $entityManager The entity manager service
	 * @param UserRepository $entityRepository The entity repository
	 * 
	 * @return static An entity controller
	 */
	public function __construct(EntityManagerInterface $entityManager, UserRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * Cette route permet d'afficher la page du compte utilisateur
	 * 
	 * @param Request $request La requête HTTP
	*/
	#[Route('/profile', name: 'show', methods: ['GET'])]
	public function show(Request $request): Response
	{
		// Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
		if (!$this->getUser()) {
			return $this->redirectToRoute('app_user_login');
		}

		// Rediriger l'utilisateur vers la page d'édition de son profil
		return $this->redirectToRoute('app_user_edit', ['id' => $this->getUser()->getId()]);
	}

	/**
	 * Cette route permet de lister les utilisateurs pour l'administration
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function admin(Request $request): Response
	{
		// // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté pour accéder à cette page !');
			return $this->redirectToRoute('app_user_login');
		}
		
		// Si l'utilisateur n'est pas admin
		if (!$this->isGranted('ROLE_ADMIN')) {
			$this->addFlash('error', 'Vous n\'avez pas le droit d\'accéder à cette page.');
			return $this->redirectToRoute('app_user_show');
		}

		// Récupérer tous les utilisateurs
		$products = $this->getRepository()->findByAttributes([]);

		return $this->render('pages/user/list.html.twig', ['users' => $products]);
	}

	/**
	 * Cette route permet d'afficher la page de création d'un utilisateur
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/create', name: 'create', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
	{
		// Créer le formulaire de création à partir de sa classe (dossier Form)
		$form = $this->createForm(UserEditType::class);

		// Faire gérer la requête du formulaire par Symfony
		$form->handleRequest($request);

		// Si le formulaire est soumis et valide (les contraintes de validation sont respectées)
		if ($form->isSubmitted() && $form->isValid()) {
			// Enregistrer l'utilisateur en base de données
			$user = $form->getData();
			
			try{
				$this->getEntityManager()->beginTransaction();

				// Encoder le mot de passe
				$user->setPassword(
					$userPasswordHasher->hashPassword(
						$user,
						$form->get('password')->getData()
					)
				);

				$this->getRepository()->save($user, true);
				$this->getEntityManager()->commit();

				$this->addFlash('success', 'L\'utilisateur a bien été créé !');
				return $this->redirectToRoute('app_user_list');

			} catch(\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de l\'utilisateur.');
				return $this->redirectToRoute('app_user_register');
			}
		}

		return $this->render('pages/user/create.html.twig', ['creationForm' => $form->createView()]);
	}

	/**
	 * Cette route permet d'éditer la page du compte utilisateur
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
	// Note: Le type mixed est nécessaire pour ne pas générer d'erreur lors de l'appel à
	// une route type /user/abc/edit, la vérification du type sera donc faite manuellement
	public function edit(Request $request, mixed $id, UserPasswordHasherInterface $userPasswordHasher): Response
	{
		// Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté pour éditer ce profil !');
			return $this->redirectToRoute('app_user_login');
		}

		// Si l'utilisateur n'est pas admin et qu'il n'est pas sur son propre profil, on le redirige vers son profil
		if (!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getId() != $id) {
			$this->addFlash('error', 'Vous n\'avez pas le droit d\'accéder à cette page.');
			return $this->redirectToRoute('app_user_show');
		}

		if (!is_numeric($id)) {
			$this->addFlash('error', 'L\'identifiant de l\'utilisateur doit être un nombre.');
			return $this->redirectToRoute('app_user_list');
		}

		$user = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if(!$user == $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
			// Si l'utilisateur n'est pas déjà sur la route de son profil, on le redirige vers son profil
			return $this->redirectToRoute('app_user_edit', ['id' => $this->getUser()->getId()]);
		}

		if (!$user) {
			$this->addFlash('error', 'L\'utilisateur n\'existe pas ou a été supprimé.');
			return $this->redirectToRoute('app_user_list');
		}

		// Créer le formulaire de modification à partir de sa classe (dossier Form)
		// selon le rôle de l'utilisateur
		$form = null;
		if($this->isGranted('ROLE_ADMIN')) {
			$form = $this->createForm(UserEditType::class, $user);
		} else {
			$form = $this->createForm(ProfileType::class, $user);
		}

		// Faire gérer la requête du formulaire par Symfony
		$form->handleRequest($request);

		// Si le formulaire est soumis et valide (les contraintes de validation sont respectées)
		if ($form->isSubmitted() && $form->isValid()) {
			$user = $form->getData();

			// Encoder le mot de passe
			$user->setPassword(
				$userPasswordHasher->hashPassword(
					$user,
					$form->get('password')->getData()
				)
			);
			try{
				$this->getEntityManager()->beginTransaction();

				// Enregistrer les modifications en base de données
				$this->getRepository()->save($user, true);

				$this->getEntityManager()->commit();

				$this->addFlash('success', 'Vos modifications ont été appliquées avec succès.');
				return $this->redirectToRoute('app_user_show');

			} catch(\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement des modifications.');
				return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
			}
		}

		return $this->render('pages/user/edit.html.twig', [
			'editionForm' => $form->createView(),
			'user' => $user
		]);
	}

	/**
	 * Cette route permet d'afficher la page d'inscription
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/register', name: 'register', methods: ['GET', 'POST'])]
	public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
	{
		// Créer un nouvel utilisateur
		$user = new User();
		// Créer le formulaire d'inscription à partir de sa classe (dossier Form)
		$form = $this->createForm(RegistrationType::class, $user);
		// Faire gérer la requête du formulaire par Symfony
		$form->handleRequest($request);


		// Si le formulaire est soumis et valide (les contraintes de validation sont respectées)
		if ($form->isSubmitted() && $form->isValid()) {
			// Encoder le mot de passe
			$user->setPassword(
				$userPasswordHasher->hashPassword(
					$user,
					$form->get('password')->getData()
				)
			);
			try{
				$this->getEntityManager()->beginTransaction();

				// Enregistrer l'utilisateur en base de données
				$this->getRepository()->save($user, true);

				$this->getEntityManager()->commit();

				// Authentifier l'utilisateur
				return $userAuthenticator->authenticateUser(
					$user,
					$authenticator,
					$request
				);

			} catch(\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de l\'utilisateur.');
				return $this->redirectToRoute('app_user_register');
			}
		}

		return $this->render('pages/user/register.html.twig', [
			'registrationForm' => $form->createView(),
		]);
	}

	/**
	 * Cette route permet d'afficher la page de connexion
	 * 
	 * @param AuthenticationUtils $authenticationUtils Les utilitaires d'authentification
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
	public function login(AuthenticationUtils $authenticationUtils): Response
	{
		// Si l'utilisateur cherche à accéder à la page de connexion 
		// alors qu'il est déjà connecté, on le redirige vers la page de son compte
		if ($this->getUser()) {
			return $this->redirectToRoute('app_user_show');
		}

		// Stocker les erreurs de connexion s'il y en a
		$error = $authenticationUtils->getLastAuthenticationError();
		if ($error) {
			// Note: on utilise getMessageKey et non pas getMessage car getMessageKey
			// retourne une chaîne de caractères qui peut éventuellement être traduite
			// par le système de traduction de Symfony
			$errorToDisplay = $error->getMessageKey(). PHP_EOL;

			// Si l'environnement est de type dev ou local, on affiche les détails de l'erreur
			$currentEnv = $this->getParameter('kernel.environment');
			if ($currentEnv === 'dev' || $currentEnv === 'local') {
				$errorToDisplay .= 'Données: ' . print_r($error->getMessageData(), true) . PHP_EOL;
				$errorToDisplay .= 'Code d\'erreur: ' . $error->getCode() . PHP_EOL;
				$errorToDisplay .= 'Fichier: ' . $error->getFile() . PHP_EOL;
				$errorToDisplay .= 'Ligne: ' . $error->getLine();
			}
			// Un flash peut être récupéré une seule fois un template Twig avec la variable globale app.flashes
			// Il est stocké en session et supprimé dès qu'il est affiché
			$this->addFlash('error', $errorToDisplay);
		}

		// Récupérer le dernier nom d'utilisateur saisi par l'utilisateur
		$lastUsername = $authenticationUtils->getLastUsername();

		// Créer le formulaire de connexion à partir de sa classe (dossier Form)
		$form = $this->createForm(LoginType::class);

		return $this->render('pages/user/login.html.twig', ['last_username' => $lastUsername, 'loginForm' => $form->createView()]);
	}

	/**
	 * Cette route permet en principe de déconnecter l'utilisateur,
	 * mais elle ne sera jamais atteinte car la déconnexion est gérée par le pare-feu
	 * qui intercepte la requête avant qu'elle n'atteigne cette méthode
	 * 
	 * @throws \LogicException Si cette méthode est atteinte (ce qui ne devrait pas arriver)
	 */
	#[Route(path: '/logout', name: 'logout')]
	public function logout(): void
	{
		// Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion sur votre pare-feu (voir security.yaml)
		throw new \LogicException('Une erreur est survenue pendant la déconnexion. Veuillez contacter l\'administrateur du site.');
	}

	/**
	 * Cette route permet de supprimer le compte utilisateur
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
	public function delete(Request $request, int $id): Response
	{
		$connectedUser = $this->getUser();
		if (!$connectedUser) {
			$this->addFlash('error', 'Vous devez être connecté pour supprimer un compte !');
			return $this->redirectToRoute('app_user_login');
		}

		$userToRemove = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if (is_null($userToRemove)) {
			$this->addFlash('error', 'Le compte n\'existe pas ou a déjà été supprimé.');
		}
		else{
			if ($userToRemove !== $connectedUser && !$this->isGranted('ROLE_ADMIN')) {
				$this->addFlash('error', 'Vous n\'avez pas le droit de supprimer ce compte.');
				return $this->redirectToRoute('app_user_list');
			}
			// Pour déconnecter manuellement l'utilisateur si c'est lui qui est supprimé:
			if($userToRemove === $connectedUser) {
				// On supprime le jeton d'authentification dans le service de sécurité
				// du conteneur de services
				$this->container->get('security.token_storage')->setToken(null);
				// On supprime la session
				$request->getSession()->invalidate();
			}

			try{
				$this->getEntityManager()->beginTransaction();
				
				// Supprimer le compte utilisateur
				$this->getRepository()->delete($userToRemove, true);

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Le compte a bien été supprimé !');
			} catch(\Exception $e) {
				$this->addFlash('error', 'Une erreur est survenue lors de la suppression du compte.');
			}
		}

		// Si l'utilisateur est admin, on le redirige vers la liste des utilisateurs
		// Sinon, il s'agit de l'utilisateur qui a supprimé son compte, on le redirige vers la page d'accueil
		return $this->isGranted('ROLE_ADMIN') ? $this->redirectToRoute('app_user_list') : $this->redirectToRoute('app_user_login');
	}
}
