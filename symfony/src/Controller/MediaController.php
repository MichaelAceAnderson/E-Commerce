<?php

namespace App\Controller;

use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/medias', name: 'app_media_')]
class MediaController extends AbstractEntityController
{
	/**
	 * Créer le contrôleur de l'entité
	 * 
	 * @param EntityManagerInterface $entityManager Le service de gestion des entités
	 * @param MediaRepository $entityRepository Le repository de l'entité
	 * 
	 * @return static Un contrôleur pour l'entité
	 */
	public function __construct(EntityManagerInterface $entityManager, MediaRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * Cette route permet de lister les médias pour l'administration
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Vous n\'avez pas le droit d\'accéder à cette page.')]
	public function admin(Request $request): Response
	{
		// Récupérer tous les médias
		$medias = $this->getRepository()->findByAttributes([]);

		return $this->render('pages/media/list.html.twig', ['medias' => $medias]);
	}

	/**
	 * Cette route permet de supprimer un média
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
		
		$media = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;

		if (!$media) {
			$this->addFlash('error', 'Le média n\'existe pas ou a été supprimée.');
		} else {
			try{
				$this->getEntityManager()->beginTransaction();

				// Récupérer le chemin du fichier média à supprimer
				$mediaPath = $this->getParameter('kernel.project_dir').'/public'.$media->getPath();

				$this->getRepository()->delete($media);
				
				// Si aucune erreur n'est survenue, supprimer le fichier média
				if(file_exists($mediaPath))
				{
					unlink($mediaPath);
				}

				$this->getEntityManager()->flush();
				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Le média a bien été supprimée !');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la suppression du média.');
				if ($currentEnv === 'dev')
				{
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		return $this->redirectToRoute('app_media_list');
	}


	/**
	 * Cette route permet d'afficher la page d'un média
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(Request $request, $id): Response
	{
		if (!is_numeric($id)) {
			$this->addFlash('error', 'L\'identifiant du média doit être un nombre.');
			return $this->redirectToRoute('app_product_search');
		}

		$media = $this->getRepository()->findByAttributes(['id' => $id])[0] ?? null;
		if (!$media) {
			$this->addFlash('error', 'Le média n\'existe pas ou a été supprimée.');
		}

		return $this->redirectToRoute('app_media_list');
	}
}