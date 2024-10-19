<?php

namespace App\Controller;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/error', name: 'app_error_')]
class ErrorController extends AbstractController
{

	public function returnMsg(int $code){
		$message = match ($code) {
			404 => 'La page demandée est introuvable !',
			403 => 'Vous n\'avez pas le droit d\'accéder à cette page !',
			500 => 'Une erreur est survenue sur le serveur !',
			default => 'Une erreur est survenue ! Contactez l\'administrateur du site si le problème persiste',
		};
		return $message;
	}

	/**
	 * Récupère l'exception et affiche le message d'erreur (automatiquement via la configuration dans config/packages/twig.yaml)
	 * 
	 * @param FlattenException $exception L'exception levée
	 * 
	 * @return Response
	 */
    public function showError(FlattenException $exception): Response
    {
		$message = '';
		// Si APP_ENV est en mode dev, on affiche le message de l'exception
		if ($this->getParameter('kernel.environment') === 'dev') {
			$message = $exception->getStatusText() . ': ' . $exception->getMessage()
			.PHP_EOL.' à la ligne '.$exception->getLine().' dans le fichier '.$exception->getFile() . ':'
			.PHP_EOL.PHP_EOL.$exception->getTraceAsString();
		}
		else{
			$message = $this->returnMsg($exception->getStatusCode());
		}

		return new Response(
			$this->renderView('pages/error.html.twig', [
				'statusCode' => $exception->getStatusCode(),
				'message' => $message
			]),
			$exception->getStatusCode()
		);
    }
	
	/**
	 * Affiche une page d'erreur en fonction du code d'erreur
	 * 
	 * @param int $statusCode Le code d'erreur
	 * 
	 * @return Response
	 */
	#[Route('/{statusCode}', name: 'show')]
	public function show(int $statusCode): Response
	{
		$message = $this->returnMsg($statusCode);

		return new Response(
			$this->renderView('pages/error.html.twig', [
				'statusCode' => $statusCode,
				'message' => $message
			]),
			$statusCode
		);
	}
}