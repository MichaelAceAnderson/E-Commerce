<?php

namespace App\Controller;

use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/medias', name: 'app_media_')]
class MediaController extends AbstractEntityController
{
	/**
	 * Create the entity controller
	 * 
	 * @param EntityManagerInterface $entityManager The entity manager service
	 * @param MediaRepository $repository The entity repository
	 * 
	 * @return static An entity controller
	 */
	public function __construct(EntityManagerInterface $entityManager, MediaRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * This route allows listing media for administration
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'You do not have permission to access this page.')]
	public function admin(Request $request): Response
	{
		// Retrieve all media
		$medias = $this->getRepository()->findByAttributes([]);

		return $this->render('pages/media/list.html.twig', ['medias' => $medias]);
	}

	/**
	 * This route allows deleting a media
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
		
		$media = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if (!$media) {
			$this->addFlash('error', 'The media does not exist or has been deleted.');
		} else {
			try {
				$this->getEntityManager()->beginTransaction();

				// Retrieve the media file path to delete
				$mediaPath = $this->getParameter('kernel.project_dir').'/public'.$media->getPath();

				$this->getRepository()->delete($media);
				
				// If no error occurred, delete the media file
				if (file_exists($mediaPath)) {
					unlink($mediaPath);
				}

				$this->getEntityManager()->flush();
				$this->getEntityManager()->commit();
				$this->addFlash('success', 'The media has been successfully deleted!');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while deleting the media.');
				if ($currentEnv === 'dev') {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->redirectToRoute('app_media_list');
	}

	/**
	 * This route allows displaying a media page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(Request $request, $id): Response
	{
		if (!is_numeric($id)) {
			$this->addFlash('error', 'The media ID must be a number.');
			return $this->redirectToRoute('app_product_search');
		}

		$media = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$media) {
			$this->addFlash('error', 'The media does not exist or has been deleted.');
		}

		return $this->redirectToRoute('app_media_list');
	}
}
