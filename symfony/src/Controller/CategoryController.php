<?php

namespace App\Controller;

use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categories', name: 'app_category_')]
class CategoryController extends AbstractEntityController
{
	/**
	 * Créer le contrôleur de l'entité
	 * 
	 * @param EntityManagerInterface $entityManager Le service de gestion des entités
	 * @param CategoryRepository $entityRepository Le repository de l'entité
	 * 
	 * @return static Un contrôleur pour l'entité
	 */
	public function __construct(EntityManagerInterface $entityManager, CategoryRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * Cette route permet de lister les catégories pour l'administration
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function admin(Request $request): Response
	{
		// Récupérer tous les catégories
		$categories = $this->getRepository()->findByAttributes([]);

		return $this->render('pages/category/list.html.twig', ['categories' => $categories]);
	}

	/**
	 * Cette route permet d'afficher la page de création d'une catégorie
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/create', name: 'create', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function create(Request $request): Response
	{
		// Récupérer l'environnement actuel pour afficher les erreurs de développement
		$currentEnv = $this->getParameter('kernel.environment');

		// Créer le formulaire de création à partir de sa classe (dossier Form)
		$form = $this->createForm(CategoryType::class);

		// Faire gérer la requête du formulaire par Symfony
		$form->handleRequest($request);

		// Si le formulaire est soumis et valide (les contraintes de validation sont respectées)
		if ($form->isSubmitted() && $form->isValid()) {
			$category = $form->getData();
			try{
				$this->getEntityManager()->beginTransaction();

				// Récupérer le media à enregistrer
				$media = $category->getMedia();
				
				// Récupérer le fichier uploadé s'il y en a un et le traiter
				if($media && $file = $media->getFile())
				{
					$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
					// Convertir les caractères spéciaux en caractères ASCII
					$safeFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename);
					// Remplacer les caractères non autorisés par des tirets
					$safeFilename = preg_replace('/[^A-Za-z0-9_\-]/i', '-', $safeFilename);
					// Mettre le nom de fichier en minuscules
					$safeFilename = strtolower($safeFilename);
					// Ajouter un identifiant unique pour éviter les collisions de noms de fichiers
					$newFilename = $safeFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

					// Déplacer le fichier dans le répertoire de votre choix
					try {
						$file->move(
							$this->getParameter('media_dir_absolute'),
							$newFilename
						);
						if($currentEnv == 'dev')
						{
							$this->addFlash('info', 'Média uploadé à '. $this->getParameter('media_dir_absolute').$newFilename);
						}
					} catch (FileException $e) {
						// Générer une erreur qui sera attrapée par le bloc catch parent de celui-ci
						throw new \Exception('Une erreur est survenue lors de l\'upload du fichier dans '.$this->getParameter('media_dir_absolute').$newFilename.':'.PHP_EOL.''.$e->getMessage());
					}

					// Mettre à jour le média
					$media->setPath($this->getParameter('media_dir_relative').$newFilename);
					if($media->getAlt() == null)
					{
						$media->setAlt($originalFilename);
					}
					$media->setType($file->getClientMimeType());
					$this->getEntityManager()->persist($media);
				}
				// Enregistrer la catégorie en base de données
				$this->getRepository()->save($category, false);

				$this->getEntityManager()->flush();

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'La catégorie a bien été créée !');
				return $this->redirectToRoute('app_category_list');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la création de la catégorie. Assurez-vous d\'avoir correctement rempli les champs affichés (et notamment le fichier média).');
				if($currentEnv == 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/category/create.html.twig', ['creationForm' => $form->createView()]);
	}

	/**
	 * Cette route permet d'éditer une catégorie
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function edit(Request $request, int $id): Response
	{
		$category = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$category) {
			$this->addFlash('error', 'La catégorie n\'existe pas ou a été supprimée.');
			return $this->redirectToRoute('app_error_show', ['statusCode' => 404]);
		}

		// Créer le formulaire d'édition à partir de sa classe (dossier Form)
		$form = $this->createForm(CategoryType::class, $category);

		// Faire gérer la requête du formulaire par Symfony
		$form->handleRequest($request);

		// Si le formulaire est soumis et valide (les contraintes de validation sont respectées)
		if ($form->isSubmitted() && $form->isValid()) {
			try{
				// Récupérer l'environnement actuel pour afficher les erreurs de développement
				$currentEnv = $this->getParameter('kernel.environment');

				$this->getEntityManager()->beginTransaction();

				// Récupérer le media à enregistrer
				$media = $category->getMedia();
				
				// Récupérer le fichier uploadé s'il y en a un et le traiter
				if($media && $file = $media->getFile())
				{
					$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
					// Convertir les caractères spéciaux en caractères ASCII
					$safeFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename);
					// Remplacer les caractères non autorisés par des tirets
					$safeFilename = preg_replace('/[^A-Za-z0-9_\-]/i', '-', $safeFilename);
					// Mettre le nom de fichier en minuscules
					$safeFilename = strtolower($safeFilename);
					// Ajouter un identifiant unique pour éviter les collisions de noms de fichiers
					$newFilename = $safeFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

					// Déplacer le fichier dans le répertoire de votre choix
					try {
						$file->move(
							$this->getParameter('media_dir_absolute'),
							$newFilename
						);
						if($currentEnv == 'dev')
						{
							$this->addFlash('info', 'Média uploadé à '. $this->getParameter('media_dir_absolute').$newFilename);
						}
					} catch (FileException $e) {
						// Générer une erreur qui sera attrapée par le bloc catch parent de celui-ci
						throw new \Exception('Une erreur est survenue lors de l\'upload du fichier dans '.$this->getParameter('media_dir_absolute').$newFilename.':'.PHP_EOL.''.$e->getMessage());
					}

					// Mettre à jour le média
					$media->setPath($this->getParameter('media_dir_relative').$newFilename);
					if($media->getAlt() == null)
					{
						$media->setAlt($originalFilename);
					}
					$media->setType($file->getClientMimeType());
					$this->getEntityManager()->persist($media);
				}
				// Enregistrer la catégorie en base de données
				$this->getRepository()->save($category, false);

				$this->getEntityManager()->flush();

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'La catégorie a bien été éditée !');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de l\'édition de la catégorie.');
				if ($currentEnv === 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/category/edit.html.twig', ['editionForm' => $form->createView(), 'category' => $category]);
	}

	/**
	 * Cette route permet de supprimer une catégorie
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function delete(Request $request, int $id): Response
	{
		// Récupérer l'environnement actuel pour afficher les erreurs de développement
		$currentEnv = $this->getParameter('kernel.environment');
		
		$category = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if (!$category) {
			$this->addFlash('error', 'La catégorie n\'existe pas ou a été supprimée.');
		} else {
			try{
				$this->getEntityManager()->beginTransaction();

				// Si le produit a un média, on les détache du produit
				if($category->getMedia())
				{
					// Récupérer le chemin du fichier média à supprimer
					$mediaPath = $this->getParameter('kernel.project_dir').'/public'.$category->getMedia()->getPath();

					$this->getRepository()->delete($category->getMedia());
					
					// Si aucune erreur n'est survenue, supprimer le fichier média
					if(file_exists($mediaPath))
					{
						unlink($mediaPath);
					}
				}
				$this->getRepository()->delete($category);

				$this->getEntityManager()->flush();
				$this->getEntityManager()->commit();
				$this->addFlash('success', 'La catégorie a bien été supprimée !');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la suppression de la catégorie.');
				if ($currentEnv === 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->redirectToRoute('app_category_list');
	}


	/**
	 * Cette route permet d'afficher la page d'une catégorie
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(Request $request, $id): Response
	{
		if (!is_numeric($id)) {
			$this->addFlash('error', 'L\'identifiant de la catégorie doit être un nombre.');
			return $this->redirectToRoute('app_product_search');
		}

		$category = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$category) {
			$this->addFlash('error', 'La catégorie n\'existe pas ou a été supprimée.');
		}

		return $this->redirectToRoute('app_product_search', ['category' => $category->getId()]);
	}
}