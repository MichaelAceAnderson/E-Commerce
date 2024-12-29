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
	 * Create the entity controller
	 * 
	 * @param EntityManagerInterface $entityManager The entity manager service
	 * @param ProductRepository $entityRepository The entity repository
	 * 
	 * @return static An entity controller
	 */
	public function __construct(EntityManagerInterface $entityManager, ProductRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * This route displays the search page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/search', name: 'search', methods: ['GET'])]
	public function search(Request $request): Response
	{
		// Get search parameters from the GET request
		$params = $request->query->all();

		// Separate entity attribute filters from other parameters
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

		// Get all items matching the filters in non-strict mode
		// since strict mode is too restrictive, we will manually filter later
		$items = $productRepository->findByAttributes($entityFilters, false, $orderBy);
		// Keep only those whose price is within the specified range
		if (isset($params['minPrice']) && isset($params['maxPrice'])) {
			$items = array_filter($items, function ($item) use ($params) {
				return $item->getPrice() >= $params['minPrice'] && $item->getPrice() <= $params['maxPrice'];
			});
		}

		// Note: Since the search is not in strict mode, it will also retrieve items 
		// that do not belong to the specified category in the filters
		// We must manually filter here to keep only those that match
		if (isset($params['category']) && !empty($params['category'])) {
			$items = array_filter($items, function ($item) use ($params) {
				return $item->getCategory()->getId() == $params['category'];
			});
		}

		// Note: Since the search is not in strict mode, it will also retrieve items 
		// that do not contain the specified string in the filters
		// We must manually filter here to keep only those that match
		if (isset($params['name']) && !empty($params['name'])) {
			$items = array_filter($items, function ($item) use ($params) {
				return strpos(strtolower($item->getName()), strtolower($params['name'])) !== false;
			});
		}

		// Get all categories for the search filter
		$categoryRepository = $this->getEntityManager()->getRepository('\App\Entity\Category');
		$categories = $categoryRepository->findByAttributes([], false, ['name', 'ASC']);

		// Get the highest and lowest price for the search filter
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
	 * This route lists products for administration
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function admin(Request $request): Response
	{
		// Get all products
		$products = $this->getRepository()->findByAttributes([]);

		return $this->render('pages/product/list.html.twig', ['products' => $products]);
	}

	/**
	 * This route displays the product creation page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/create', name: 'create', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function create(Request $request): Response
	{
		// Get the current environment to display development errors
		$currentEnv = $this->getParameter('kernel.environment');

		// Create the creation form from its class (Form folder)
		$form = $this->createForm(ProductEditType::class);

		// Let Symfony handle the form request
		$form->handleRequest($request);

		// If the form is submitted and valid (validation constraints are met)
		if ($form->isSubmitted() && $form->isValid()) {
			$product = $form->getData();
			try{
				$this->getEntityManager()->beginTransaction();

				// Save the product to the database
				$this->getRepository()->save($product, false);

				// Save the product's media to the database
				$medias = $product->getMedias();
				foreach ($medias as $media) {
					// Get the uploaded file if there is one and process it
					$file = $media->getFile();
					if($file)
					{
						$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
						// Convert special characters to ASCII
						$safeFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename);
						// Replace unauthorized characters with dashes
						$safeFilename = preg_replace('/[^A-Za-z0-9_\-]/i', '-', $safeFilename);
						// Convert the filename to lowercase
						$safeFilename = strtolower($safeFilename);
						// Add a unique identifier to avoid filename collisions
						$newFilename = $safeFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

						// Move the file to the directory of your choice
						try {
							$file->move(
								$this->getParameter('media_dir_absolute'),
								$newFilename
							);
							if($currentEnv == 'dev')
							{
								$this->addFlash('info', 'Media uploaded to '. $this->getParameter('media_dir_absolute').$newFilename);
							}
						} catch (FileException $e) {
							// Generate an error that will be caught by the parent catch block
							throw new \Exception('An error occurred while uploading the file to '.$this->getParameter('media_dir_absolute').$newFilename.':'.PHP_EOL.''.$e->getMessage());
						}

						// Update the media
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
				$this->addFlash('success', 'The product has been successfully created!');
				return $this->redirectToRoute('app_product_list');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while creating the product. Make sure you have filled in the displayed fields correctly (including media files).');
				if($currentEnv == 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/product/create.html.twig', ['creationForm' => $form->createView()]);
	}

	/**
	 * This route allows editing a product
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function edit(Request $request, int $id): Response
	{
		$product = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$product) {
			$this->addFlash('error', 'The product does not exist or has been deleted.');
			return $this->redirectToRoute('app_error_show', ['statusCode' => 404]);
		}

		// Create the edit form from its class (Form folder)
		$form = $this->createForm(ProductEditType::class, $product);

		// Let Symfony handle the form request
		$form->handleRequest($request);

		// If the form is submitted and valid (validation constraints are met)
		if ($form->isSubmitted() && $form->isValid()) {
			try{
				// Get the current environment to display development errors
				$currentEnv = $this->getParameter('kernel.environment');

				$this->getEntityManager()->beginTransaction();

				// Save the product to the database
				$this->getRepository()->save($product, true);

				// Save the product's media to the database
				$medias = $product->getMedias();
				foreach ($medias as $media) {
					// Get the uploaded file if there is one and process it
					$file = $media->getFile();
					if($file)
					{
						$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
						// Convert special characters to ASCII
						$safeFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename);
						// Replace unauthorized characters with dashes
						$safeFilename = preg_replace('/[^A-Za-z0-9_\-]/i', '-', $safeFilename);
						// Convert the filename to lowercase
						$safeFilename = strtolower($safeFilename);
						// Add a unique identifier to avoid filename collisions
						$newFilename = $safeFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

						// Move the file to the directory of your choice
						try {
							$file->move(
								$this->getParameter('media_dir_absolute'),
								$newFilename
							);
							if($currentEnv == 'dev')
							{
								$this->addFlash('info', 'Media uploaded to '. $this->getParameter('media_dir_absolute').$newFilename);
							}
						} catch (FileException $e) {
							// Generate an error that will be caught by the parent catch block
							throw new \Exception('An error occurred while uploading the file to '.$this->getParameter('media_dir_absolute').$newFilename.':'.PHP_EOL.''.$e->getMessage());
						}

						// Update the media
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
				$this->addFlash('success', 'The product has been successfully edited!');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while editing the product. Make sure you have filled in the displayed fields correctly (including media files).');
				if ($currentEnv === 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/product/edit.html.twig', ['editionForm' => $form->createView(), 'product' => $product]);
	}

	/**
	 * This route allows deleting a product
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function delete(Request $request, int $id): Response
	{
		$product = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if (!$product) {
			$this->addFlash('error', 'The product does not exist or has already been deleted.');
		} else {
			try{
				$this->getEntityManager()->beginTransaction();

				// If the product has one or more media, delete them
				foreach ($product->getMedias() as $media) {
					
					// Get the path of the media file to delete
					$mediaPath = $this->getParameter('kernel.project_dir').'/public'.$media->getPath();

					$product->removeMedia($media);
					$this->getEntityManager()->remove($media);
					
					// If no error occurred, delete the media file
					if(file_exists($mediaPath))
					{
						unlink($mediaPath);
					}
				}
				$this->getRepository()->delete($product, true);


				$this->getEntityManager()->commit();
				$this->addFlash('success', 'The product has been successfully deleted!');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while deleting the product media.');
			}
		}

		return $this->redirectToRoute('app_product_list');
	}


	/**
	 * This route displays a product page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(Request $request, $id): Response
	{
		if (!is_numeric($id)) {
			$this->addFlash('error', 'The product ID must be a number.');
			return $this->redirectToRoute('app_product_search');
		}

		$product = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$product) {
			$this->addFlash('error', 'The product does not exist or has been deleted.');
		}

		return $this->render('pages/product/show.html.twig', $product ? ['product' => $product] : []);
	}
}