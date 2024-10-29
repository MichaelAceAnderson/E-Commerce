<?php

namespace App\Controller;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/error', name: 'app_error_')]
class ErrorController extends AbstractController
{

	public function returnMsg(int $code){
		$message = match ($code) {
			404 => 'The requested page was not found!',
			403 => 'You do not have permission to access this page!',
			500 => 'An error occurred on the server!',
			default => 'An error occurred! Contact the site administrator if the problem persists.',
		};
		return $message;
	}

	/**
	 * Retrieves the exception and displays the error message (automatically via the configuration in config/packages/twig.yaml)
	 * 
	 * @param FlattenException $exception The raised exception
	 * 
	 * @return Response
	 */
	public function showError(FlattenException $exception): Response
	{
		$message = '';
		// If APP_ENV is in dev mode, display the exception message
		if ($this->getParameter('kernel.environment') === 'dev') {
			$message = $exception->getStatusText() . ': ' . $exception->getMessage()
			.PHP_EOL.' at line '.$exception->getLine().' in file '.$exception->getFile() . ':'
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
	 * Displays an error page based on the error code
	 * 
	 * @param int $statusCode The error code
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