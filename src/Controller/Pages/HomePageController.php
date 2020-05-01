<?php


namespace App\Controller\Pages;


use App\Form\Dictionary\CreateTagFormType;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{

    /**
     * @Route("/homepage", name="homepage")
     * @param TagRepository $repository
     * @return Response
     */
    public function homePage(TagRepository $repository)
    {
      //  $tags = $repository->findAll();
      //  return $this->render('homepage/homepage.html.twig');
     //  return new Response('test');
    }

}