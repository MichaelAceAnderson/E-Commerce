<?php

namespace App\Controller; 

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/', name: 'app_')]
class AppController extends AbstractController
{

	/**
	 * This route displays the home page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/', name: 'home', methods: ['GET'])]
	public function index(Request $request): Response
	{	
		return $this->render('pages/index.html.twig', []);
	}


	/**
	 * This route displays the help page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/help/', name: 'help', methods: ['GET'])]
	public function help(Request $request): Response
	{	
		return $this->render('pages/help.html.twig', []);
	}

	/**
	 * This route allows front-end or back-end tests
	 * using templates and GET request parameters
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/test/', name: 'test', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Access denied')]
	public function test(Request $request): Response
	{
		// Retrieve all GET request parameters
		$params = $request->query->all();

		// Dissociate parameters already processed in this method
		// and that should not be passed to templates
		$page = $params['page'] ?? 'index';
		$request->query->remove('page');
		unset($params['page']);

		$templateDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates';
		$templateRelativePath = 'tests' . DIRECTORY_SEPARATOR . $page . '.html.twig';
		$templateAbsolutePath = $templateDir . DIRECTORY_SEPARATOR . $templateRelativePath;

		if(!file_exists($templateAbsolutePath) || $page == 'index') {
			// Content of the tests folder without traversing . and .. directories
			$fileList = array_diff(scandir(pathinfo($templateAbsolutePath, PATHINFO_DIRNAME)), array('..', '.'));

			// Generate a list of links to test pages
			$templateList = [];
			foreach ($fileList as $template) {
				// Retrieve the file name (without path or .html.twig extension)
				$templateFileName = pathinfo($template, PATHINFO_FILENAME);
				$templateName = preg_replace('/\.[^.]*$/', '', $templateFileName);

				$currentRoute = $request->attributes->get('_route');
				// Generate the URL of the test page
				$templateUrl = $this->generateUrl($currentRoute, ['page' => $templateName]);

				$templateList[$templateName] = $templateUrl;
			}
			return $this->render('tests/index.html.twig', ['testList' => $templateList]);
		} else {
			return $this->render($templateRelativePath, $params);
		}
	}
}