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
	 * Create the entity controller
	 * 
	 * @param EntityManagerInterface $entityManager The entity manager service
	 * @param CategoryRepository $entityRepository The entity repository
	 * 
	 * @return static An entity controller
	 */
	public function __construct(EntityManagerInterface $entityManager, CategoryRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * This route allows listing categories for administration
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function admin(Request $request): Response
	{
		// Retrieve all categories
		$categories = $this->getRepository()->findByAttributes([]);

		return $this->render('pages/category/list.html.twig', ['categories' => $categories]);
	}

	/**
	 * This route allows displaying the category creation page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/create', name: 'create', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function create(Request $request): Response
	{
		// Retrieve the current environment to display development errors
		$currentEnv = $this->getParameter('kernel.environment');

		// Create the creation form from its class (Form folder)
		$form = $this->createForm(CategoryType::class);

		// Let Symfony handle the form request
		$form->handleRequest($request);

		// If the form is submitted and valid (validation constraints are met)
		if ($form->isSubmitted() && $form->isValid()) {
			$category = $form->getData();
			try {
				$this->getEntityManager()->beginTransaction();

				// Retrieve the media to be saved
				$media = $category->getMedia();
				
				// Retrieve the uploaded file if there is one and process it
				if ($media && $file = $media->getFile()) {
					$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
					// Convert special characters to ASCII characters
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
						if ($currentEnv == 'dev') {
							$this->addFlash('info', 'Media uploaded to '. $this->getParameter('media_dir_absolute').$newFilename);
						}
					} catch (FileException $e) {
						// Generate an error that will be caught by the parent catch block
						throw new \Exception('An error occurred while uploading the file to '.$this->getParameter('media_dir_absolute').$newFilename.':'.PHP_EOL.''.$e->getMessage());
					}

					// Update the media
					$media->setPath($this->getParameter('media_dir_relative').$newFilename);
					if ($media->getAlt() == null) {
						$media->setAlt($originalFilename);
					}
					$media->setType($file->getClientMimeType());
					$this->getEntityManager()->persist($media);
				}
				// Save the category to the database
				$this->getRepository()->save($category, false);

				$this->getEntityManager()->flush();

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'The category has been successfully created!');
				return $this->redirectToRoute('app_category_list');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while creating the category. Make sure you have correctly filled in the displayed fields (including the media file).');
				if ($currentEnv == 'dev') {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/category/create.html.twig', ['creationForm' => $form->createView()]);
	}

	/**
	 * This route allows editing a category
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function edit(Request $request, int $id): Response
	{
		$category = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$category) {
			$this->addFlash('error', 'The category does not exist or has been deleted.');
			return $this->redirectToRoute('app_error_show', ['statusCode' => 404]);
		}

		// Create the edit form from its class (Form folder)
		$form = $this->createForm(CategoryType::class, $category);

		// Let Symfony handle the form request
		$form->handleRequest($request);

		// If the form is submitted and valid (validation constraints are met)
		if ($form->isSubmitted() && $form->isValid()) {
			try {
				// Retrieve the current environment to display development errors
				$currentEnv = $this->getParameter('kernel.environment');

				$this->getEntityManager()->beginTransaction();

				// Retrieve the media to be saved
				$media = $category->getMedia();
				
				// Retrieve the uploaded file if there is one and process it
				if ($media && $file = $media->getFile()) {
					$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
					// Convert special characters to ASCII characters
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
						if ($currentEnv == 'dev') {
							$this->addFlash('info', 'Media uploaded to '. $this->getParameter('media_dir_absolute').$newFilename);
						}
					} catch (FileException $e) {
						// Generate an error that will be caught by the parent catch block
						throw new \Exception('An error occurred while uploading the file to '.$this->getParameter('media_dir_absolute').$newFilename.':'.PHP_EOL.''.$e->getMessage());
					}

					// Update the media
					$media->setPath($this->getParameter('media_dir_relative').$newFilename);
					if ($media->getAlt() == null) {
						$media->setAlt($originalFilename);
					}
					$media->setType($file->getClientMimeType());
					$this->getEntityManager()->persist($media);
				}
				// Save the category to the database
				$this->getRepository()->save($category, false);

				$this->getEntityManager()->flush();

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'The category has been successfully edited!');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while editing the category.');
				if ($currentEnv === 'dev') {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->render('pages/category/edit.html.twig', ['editionForm' => $form->createView(), 'category' => $category]);
	}

	/**
	 * This route allows deleting a category
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function delete(Request $request, int $id): Response
	{
		// Retrieve the current environment to display development errors
		$currentEnv = $this->getParameter('kernel.environment');
		
		$category = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if (!$category) {
			$this->addFlash('error', 'The category does not exist or has been deleted.');
		} else {
			try {
				$this->getEntityManager()->beginTransaction();

				// If the product has media, detach them from the product
				if ($category->getMedia()) {
					// Retrieve the path of the media file to be deleted
					$mediaPath = $this->getParameter('kernel.project_dir').'/public'.$category->getMedia()->getPath();

					$this->getRepository()->delete($category->getMedia());
					
					// If no error occurred, delete the media file
					if (file_exists($mediaPath)) {
						unlink($mediaPath);
					}
				}
				$this->getRepository()->delete($category);

				$this->getEntityManager()->flush();
				$this->getEntityManager()->commit();
				$this->addFlash('success', 'The category has been successfully deleted!');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while deleting the category.');
				if ($currentEnv === 'dev') {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->redirectToRoute('app_category_list');
	}


	/**
	 * This route allows displaying a category page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(Request $request, $id): Response
	{
		if (!is_numeric($id)) {
			$this->addFlash('error', 'The category ID must be a number.');
			return $this->redirectToRoute('app_product_search');
		}

		$category = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$category) {
			$this->addFlash('error', 'The category does not exist or has been deleted.');
		}

		return $this->redirectToRoute('app_product_search', ['category' => $category->getId()]);
	}
}
