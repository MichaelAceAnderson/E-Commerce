<?php

namespace App\Controller; 

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/', name: 'app_')]
class AppController extends AbstractController
{

	/**
	 * Cette route permet d'afficher la page d'accueil
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/', name: 'home', methods: ['GET'])]
	public function index(Request $request): Response
	{	
		return $this->render('pages/index.html.twig', []);
	}


	/**
	 * Cette route permet d'afficher la page d'aide
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/help/', name: 'help', methods: ['GET'])]
	public function help(Request $request): Response
	{	
		return $this->render('pages/help.html.twig', []);
	}

	/**
	 * Cette route permet d'effectuer des tests front-end ou back-end
	 * grâce aux templates et aux paramètres de requête GET
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/test/', name: 'test', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Accès refusé')]
	public function test(Request $request): Response
	{
		// Récupérer tous les paramètres de la requête GET
		$params = $request->query->all();

		// Dissocier les paramètres déjà traités dans cette méthode
		// et qui ne doivent pas être transmis aux templates
		$page = $params['page'] ?? 'index';
		$request->query->remove('page');
		unset($params['page']);

		$templateDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates';
		$templateRelativePath = 'tests' . DIRECTORY_SEPARATOR . $page . '.html.twig';
		$templateAbsolutePath = $templateDir . DIRECTORY_SEPARATOR . $templateRelativePath;

		if(!file_exists($templateAbsolutePath) || $page == 'index') {
			// Contenu du dossier tests sans le parcours des dossiers . et ..
			$fileList = array_diff(scandir(pathinfo($templateAbsolutePath, PATHINFO_DIRNAME)), array('..', '.'));

			// Générer une liste de liens vers les pages de test
			$templateList = [];
			foreach ($fileList as $template) {
				// Récupérer le nom du fichier (sans le chemin ni l'extension .html.twig)
				$templateFileName = pathinfo($template, PATHINFO_FILENAME);
				$templateName = preg_replace('/\.[^.]*$/', '', $templateFileName);

				$currentRoute = $request->attributes->get('_route');
				// Générer l'URL de la page de test
				$templateUrl = $this->generateUrl($currentRoute, ['page' => $templateName]);

				$templateList[$templateName] = $templateUrl;
			}
			return $this->render('tests/index.html.twig', ['testList' => $templateList]);
		} else {
			return $this->render($templateRelativePath, $params);
		}
	}
}