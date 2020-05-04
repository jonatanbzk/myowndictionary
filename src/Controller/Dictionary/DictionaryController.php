<?php


namespace App\Controller\Dictionary;

use App\Entity\Tag;
use App\Form\Dictionary\TagType;
use App\Repository\TagRepository;
use App\Repository\TermRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {

        $this->session = $session;
    }

    private function getTags($tagRepository)
    {
        $user = $this->getUser();
        return $tagRepository->findBy(array('user' => $user));
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

    private function getTerms($termRepository, $paginator, $request)
    {
        $currentTag = $this->session->get('current_tag');
            $tagId = (int) $currentTag[0];
            $terms = $paginator->paginate(
                $termRepository->findByQuery($tagId),
                $request->query->getInt('page', 1),
                10
            );
            return $terms;
    }

    /**
     * @Route("/", name="index")
     * @param TagRepository $tagRepository
     * @param TermRepository $termRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(TagRepository $tagRepository, TermRepository
    $termRepository, PaginatorInterface $paginator, Request $request)
    {
            return $this->render('homepage/homepage.html.twig', [
                'formTag' => $this->addTags($request),
                'tags' => $this->getTags($tagRepository),
                'terms' => $this->getTerms($termRepository, $paginator, $request)
        ]);
    }

    /**
     * @Route("/{id}/{tag}", name="currentTag")
     * @param Request $request
     * @param Tag $tag
     * @return RedirectResponse
     */
    public function currentTag(Request $request, Tag $tag)
    {
        $id = $request->attributes->get('_route_params');
        $urlTag = (int) $id["tag"];
        $langStr = $tag->getLangStr();
        $this->session->set('current_tag', [$urlTag, $langStr]);
        return $this->redirectToRoute('homepage_index');
    }
}