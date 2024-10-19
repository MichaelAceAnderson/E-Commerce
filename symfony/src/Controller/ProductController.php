<?php

namespace App\Controller;

use App\Form\ProductEditType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products', name: 'app_product_')]
class ProductController extends AbstractEntityController
{
	/**
	 * Créer le contrôleur de l'entité
	 * 
	 * @param EntityManagerInterface $entityManager Le service de gestion des entités
	 * @param ProductRepository $entityRepository Le repository de l'entité
	 * 
	 * @return static Un contrôleur pour l'entité
	 */
	public function __construct(EntityManagerInterface $entityManager, ProductRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * Cette route permet d'afficher la page de recherche
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/search', name: 'search', methods: ['GET'])]
	public function search(Request $request): Response
	{
		// Récupérer les paramètres de recherche de la requête GET
		$params = $request->query->all();

		// Séparer les filtres relatifs aux attributs d'entités du reste des paramètres
		$productRepository = $this->getRepository();
		$entityFilters = [];
		$entityAttributes = ProductRepository::getEntityAttributes($productRepository, true);
		foreach ($params as $key => $value) {
			if (in_array($key, $entityAttributes)) {
				if (!empty($value)) {
					$entityFilters[$key] = $value;
				}
			}
		}

		$orderBy = [$params['order'] ?? 'id', $params['direction'] ?? 'DESC'];

		// Récupérer tous les articles correspondant aux filtres en mode non strict
		// puisque le mode strict est trop restrictif, on refiltrera manuellement plus bas
		$items = $productRepository->findByAttributes($entityFilters, false, $orderBy);
		// Ne garder que ceux dont le prix est dans la fourchette de prix spécifiée
		if (isset($params['minPrice']) && isset($params['maxPrice'])) {
			$items = array_filter($items, function ($item) use ($params) {
				return $item->getPrice() >= $params['minPrice'] && $item->getPrice() <= $params['maxPrice'];
			});
		}

		// Note: Puisque la recherche n'est pas en mode strict, elle récupèrera aussi les articles 
		// qui ne font pas partie de la catégorie spécifiée dans les filtres
		// On doit donc refiltrer manuellement ici pour ne garder que ceux qui correspondent
		if (isset($params['category']) && !empty($params['category'])) {
			$items = array_filter($items, function ($item) use ($params) {
				return $item->getCategory()->getId() == $params['category'];
			});
		}

		// Note: Puisque la recherche n'est pas en mode strict, elle récupèrera aussi les articles 
		// qui ne contiennent pas la chaîne de caractères spécifiée dans les filtres
		// On doit donc refiltrer manuellement ici pour ne garder que ceux qui correspondent
		if (isset($params['name']) && !empty($params['name'])) {
			$items = array_filter($items, function ($item) use ($params) {
				return strpos(strtolower($item->getName()), strtolower($params['name'])) !== false;
			});
		}

		// Récupérer toutes les catégories pour le filtre de recherche
		$categoryRepository = $this->getEntityManager()->getRepository('\App\Entity\Category');
		$categories = $categoryRepository->findByAttributes([], false, ['name', 'ASC']);

		// Récupérer le prix le plus élevé et le prix le plus bas pour le filtre de recherche
		$highestPriceProduct = $productRepository->findByAttributes([], false, ['price', 'DESC'])[0] ?? null;
		$lowestPriceProduct = $productRepository->findByAttributes([], false, ['price', 'ASC'])[0] ?? null;
		if ($highestPriceProduct) {
			$highestPrice = $highestPriceProduct->getPrice();
		}
		if ($lowestPriceProduct) {
			$lowestPrice = $lowestPriceProduct->getPrice();
		}
		if (isset($highestPrice) && isset($lowestPrice)) {
			$priceRange = ['min' => $lowestPrice, 'max' => $highestPrice];
		} else {
			$priceRange = ['min' => 0, 'max' => 0];
		}

		return $this->render('pages/product/search.html.twig', ['items' => $items, 'categories' => $categories, 'priceRange' => $priceRange]);
	}

	/**
	 * Cette route permet de lister les produits pour l'administration
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function admin(Request $request): Response
	{
		// Récupérer tous les produits
		$products = $this->getRepository()->findByAttributes([]);

		return $this->render('pages/product/list.html.twig', ['products' => $products]);
	}

	/**
	 * Cette route permet d'afficher la page de création d'un produit
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
		$form = $this->createForm(ProductEditType::class);

		// Faire gérer la requête du formulaire par Symfony
		$form->handleRequest($request);

		// Si le formulaire est soumis et valide (les contraintes de validation sont respectées)
		if ($form->isSubmitted() && $form->isValid()) {
			$product = $form->getData();
			try{
				$this->getEntityManager()->beginTransaction();

				// Enregistrer le produit en base de données
				$this->getRepository()->save($product, false);

       			// Enregistrer les medias du produit en base de données
				$medias = $product->getMedias();
				foreach ($medias as $media) {
					// Récupérer le fichier uploadé s'il y en a un et le traiter
					$file = $media->getFile();
					if($file)
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
						$media->setProduct($product);
						$this->getEntityManager()->persist($media);
					}
				}
				$this->getEntityManager()->flush();

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Le produit a bien été créé !');
				return $this->redirectToRoute('app_product_list');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la création du produit. Assurez-vous d\'avoir correctement rempli les champs affichés (et notamment les fichiers médias).');
				if($currentEnv == 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/product/create.html.twig', ['creationForm' => $form->createView()]);
	}

	/**
	 * Cette route permet d'éditer un produit
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function edit(Request $request, int $id): Response
	{
		$product = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$product) {
			$this->addFlash('error', 'Le produit n\'existe pas ou a été supprimé.');
			return $this->redirectToRoute('app_error_show', ['statusCode' => 404]);
		}

		// Créer le formulaire d'édition à partir de sa classe (dossier Form)
		$form = $this->createForm(ProductEditType::class, $product);

		// Faire gérer la requête du formulaire par Symfony
		$form->handleRequest($request);

		// Si le formulaire est soumis et valide (les contraintes de validation sont respectées)
		if ($form->isSubmitted() && $form->isValid()) {
			try{
				// Récupérer l'environnement actuel pour afficher les erreurs de développement
				$currentEnv = $this->getParameter('kernel.environment');

				$this->getEntityManager()->beginTransaction();

				// Enregistrer le produit en base de données
				$this->getRepository()->save($product, true);

        		// Enregistrer les medias du produit en base de données
				$medias = $product->getMedias();
				foreach ($medias as $media) {
					// Récupérer le fichier uploadé s'il y en a un et le traiter
					$file = $media->getFile();
					if($file)
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
						$media->setProduct($product);
					}
					$this->getEntityManager()->persist($media);
				}
				$this->getEntityManager()->flush();

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Le produit a bien été édité !');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de l\'édition du produit. Assurez-vous d\'avoir correctement rempli les champs affichés (et notamment les fichiers médias).');
				if ($currentEnv === 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/product/edit.html.twig', ['editionForm' => $form->createView(), 'product' => $product]);
	}

	/**
	 * Cette route permet de supprimer un produit
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function delete(Request $request, int $id): Response
	{
		$product = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if (!$product) {
			$this->addFlash('error', 'Le produit n\'existe pas ou a déjà été supprimé.');
		} else {
			try{
				$this->getEntityManager()->beginTransaction();

				// Si le produit a un ou plusieurs medias, on les supprime
				foreach ($product->getMedias() as $media) {
					
					// Récupérer le chemin du fichier média à supprimer
					$mediaPath = $this->getParameter('kernel.project_dir').'/public'.$media->getPath();

					$product->removeMedia($media);
					$this->getEntityManager()->remove($media);
					
					// Si aucune erreur n'est survenue, supprimer le fichier média
					if(file_exists($mediaPath))
					{
						unlink($mediaPath);
					}
				}
				$this->getRepository()->delete($product, true);


				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Le produit a bien été supprimé !');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la suppression des medias du produit.');
			}
		}

		return $this->redirectToRoute('app_product_list');
	}


	/**
	 * Cette route permet d'afficher la page d'un produit
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(Request $request, $id): Response
	{
		if (!is_numeric($id)) {
			$this->addFlash('error', 'L\'identifiant du produit doit être un nombre.');
			return $this->redirectToRoute('app_product_search');
		}

		$product = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$product) {
			$this->addFlash('error', 'Le produit n\'existe pas ou a été supprimé.');
		}

		return $this->render('pages/product/show.html.twig', $product ? ['product' => $product] : []);
	}
}