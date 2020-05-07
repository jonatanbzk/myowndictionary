<?php


namespace App\Controller\Dictionary;

use App\Entity\Tag;
use App\Entity\Term;
use App\Form\Dictionary\TagType;
use App\Form\Dictionary\TermType;
use App\Repository\TagRepository;
use App\Repository\TermRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\This;
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

    private function addTag($request)
    {
        $user = $this->getUser();

        $tag = new Tag();
        $tag->setUser($user);

        $formTag = $this->createForm(TagType::class, $tag);
        $formTag->handleRequest($request);

        if ($formTag->isSubmitted() && $formTag->isValid()) {
            if ($formTag->get('language_1')->getData() ==
                $formTag->get('language_2')->getData()) {
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
        if ($formTag->isSubmitted() && count($formTag->getErrors()) !== 0) {
            $this->addFlash('danger', 'You already have a dictionary with these languages');
        }
        return $formTag->createView();
    }

    private function getTerms($termRepository, $paginator, $request)
    {
        $currentPage = 1;
        if (isset($_GET['page'])) {
            $currentPage = (int)$_GET['page'];
        }
        $currentTag = $this->session->get('current_tag');
            $tagId = (int) $currentTag[0];
        return $paginator->paginate(
            $termRepository->findByQuery($tagId),
            $request->query->getInt('page', $currentPage),
            10
        );
    }

    private function addTerm($request)
    {
        $currentTag = $this->session->get('current_tag');
        $currentTagId = (int) $currentTag[0];
        $repo = $this->getDoctrine()->getRepository(Tag::class);
        $tag = $repo->find($currentTagId);

        $term = new Term();
        $term->setTag($tag);
        $formTermAdd = $this->createForm(TermType::class, $term);
        $formTermAdd->handleRequest($request);

        if ($formTermAdd->isSubmitted() && $formTermAdd->isValid()) {
            $term->setAddAt(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($term);
            $entityManager->flush();
            $this->addFlash('success', 'Your word has been 
            added to you dictionary');
        }
        if ($formTermAdd->isSubmitted() && count($formTermAdd->getErrors()) !==
            0) {
        $this->addFlash('danger', 'You already have this word in
        your dictionary');
        }
        return $formTermAdd->createView();
    }

    /**
     * @Route("/word_update/{id}", name="word_update",
     *     methods="GET|POST")
     * @param Term $term
     * @param Request $request
     */
    public function editTerm(Term $term, Request $request)
    {
        if ($this->isCsrfTokenValid(
            'update' . $term->getId(), $request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $term->setWord($request->get('word'));
            $term->setTranslation($request->get('translation'));
            $entityManager->flush();
            $this->addFlash('success', 'Word modified');
            return $this->redirectToRoute('homepage_index');
        }
    }

    /**
     * @Route("/word_delete/{id}", name="word_delete", methods="DELETE")
     * @param Request $request
     * @param Term $term
     * @return RedirectResponse
     */
    public function deleteTerm(Request $request, Term $term)
    {
        if ($this->isCsrfTokenValid(
            'delete' . $term->getId(), $request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($term);
            $entityManager->flush();
            $this->addFlash('danger', 'Your word has
            been deleted');
        }
        return $this->redirectToRoute('homepage_index');
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
                'formTag' => $this->addTag($request),
                'tags' => $this->getTags($tagRepository),
                'terms' => $this->getTerms($termRepository, $paginator,
                    $request),
                'formTerm' => $this->addTerm($request),
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
        $urlParam = $request->attributes->get('_route_params');
        $urlTag = (int) $urlParam["tag"];
        $langStr = $tag->getLangStr();
        $this->session->set('current_tag', [$urlTag, $langStr]);
        return $this->redirectToRoute('homepage_index');
    }
}