<?php


namespace App\Controller\Pages;


use App\Form\Dictionary\CreateTagFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{

    /**
     * @Route("/homepage", name="homepage")
     * @return Response
     */
    public function homePage(Request $request): Response
    {
        return $this->render('homepage/homepage.html.twig');
    }

}