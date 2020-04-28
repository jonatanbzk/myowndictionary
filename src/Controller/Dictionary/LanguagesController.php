<?php


namespace App\Controller\Dictionary;

use App\Entity\Tag;
use App\Entity\User;
use App\Form\Dictionary\TagType;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LanguagesController extends AbstractController
{

    const LANGUAGES = [
        0 => 'English',
        1 => 'French',
        2 => 'German',
        3 => 'Polish',
        4 => 'Russian',
        5 => 'Italian',
        6 => 'Portuguese',
        7 => 'Spanish',
        8 => 'Esperanto'
    ];

    public function getLanguages()
    {
        return self::LANGUAGES;
    }

    /**
     * @Route("/homepage", name="tag")
     * @param Request $request
     * @return Response
     */
    public function createTag(Request $request)
    {
        $tag = new Tag();
        $user = $this->getUser();
        $tag->setUser($user);

        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('language_1')->getData() ==
                $form->get('language_2')->getData()) {
                $this->addFlash('danger', 'Please select two 
                different languages');
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($tag);
                $entityManager->flush();
                $this->addFlash('success', 'Your dictionary has 
                been created! But don\'t let it empty. Add some terms and their 
                translations :)');
            }
        }
        if ($form->isSubmitted() && count($form->getErrors()) !== 0) {
            $this->addFlash('danger', 'You already have a dictionary with these languages');
        }
            return $this->render('homepage/homepage.html.twig', [
            'formTag' => $form->createView(),
        ]);
    }

}