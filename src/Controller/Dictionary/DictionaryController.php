<?php


namespace App\Controller\Dictionary;

use App\Entity\Tag;
use App\Entity\User;
use App\Form\Dictionary\TagType;
use App\Repository\TagRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DictionaryController
 * @package App\Controller\Dictionary
 * @Route("/homepage", name="homepage_")
 */
class DictionaryController extends AbstractController
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

    private function getTags($tagRepository)
    {
        $user = $this->getUser();
        $tags = $tagRepository->findBy(array('user' => $user));
        return $tags;
    }

    private function addTags(Request $request)
    {
        $user = $this->getUser();

        $tag = new Tag();
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
        return $form->createView();
    }

    /**
     * @Route("/", name="index")
     * @param TagRepository $tagRepository
     * @param Request $request
     * @return Response
     */
    public function index(TagRepository $tagRepository, Request $request)
    {
            return $this->render('homepage/homepage.html.twig', [
                'formTag' => $this->addTags($request),
                'tags' => $this->getTags($tagRepository)

        ]);
    }

}