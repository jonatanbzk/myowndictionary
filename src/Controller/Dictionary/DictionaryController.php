<?php


namespace App\Controller\Dictionary;

use App\Entity\Tag;
use App\Entity\Term;
use App\Form\Dictionary\TagType;
use App\Form\Dictionary\TermType;
use App\Repository\TagRepository;
use App\Repository\TermRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(SessionInterface $session,
                                EntityManagerInterface $manager)
    {
        $this->session = $session;
        $this->manager = $manager;
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
                $this->manager->persist($tag);
                $this->manager->flush();
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
            $this->manager->persist($term);
            $this->manager->flush();
            $this->addFlash('success', 'Your word has been 
            added to you dictionary');
        }
        if ($formTermAdd->isSubmitted() && count($formTermAdd->getErrors(true))
            !==
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
        $termId = (int) $term->getId();
        if ($termId != 0 && $this->isCsrfTokenValid(
            'update' . $termId, $request->get('_token'))) {
            $term->setWord($request->get('word'));
            $term->setTranslation($request->get('translation'));
            $this->manager->flush();
            $this->addFlash('success', 'Word modified');
            $url = $this->session->get('url');
            return $this->redirect($url);
        }
        return;
    }

    /**
     * @Route("/word_delete/{id}", name="word_delete", methods="DELETE")
     * @param Request $request
     * @param Term $term
     * @return RedirectResponse
     */
    public function deleteTerm(Request $request, Term $term)
    {
        $termId = (int) $term->getId();
        if ($termId != 0 && $this->isCsrfTokenValid(
            'delete' . $termId, $request->get('_token'))) {
            $this->manager->remove($term);
            $this->manager->flush();
            $this->addFlash('danger', 'Your word has
            been deleted');
        }
            $url = $this->session->get('url');
            return $this->redirect($url);
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
        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->session->set('url', $currentUrl);
            return $this->render('homepage/homepage.html.twig', [
                'formTag' => $this->addTag($request),
                'tags' => $this->getTags($tagRepository),
                'formTerm' => $this->addTerm($request),
                'terms' => $this->getTerms($termRepository, $paginator,
                    $request),
        ]);
    }

    /**
     * @Route("downloadDictionary", name="downloadDictionary")
     * @param TermRepository $termRepository
     * @return RedirectResponse
     */
    public function downloadDictionary(TermRepository $termRepository)
    {
        $currentTag = $this->session->get('current_tag');
        if (!empty($currentTag)) {
            $lang1 = $currentTag[1][0];
            $lang2 = $currentTag[1][1];
            $file = $lang1 . "_" . $lang2 . "_Dictionary.txt";
            $txt = fopen($file, "w") or die("Unable to download your Dictionary!");
            fwrite($txt, "Thanks for using MyDictionary !" . PHP_EOL);
            fwrite($txt, 'Dictionary: ' . $lang1 . ' => ' . $lang2 .
                PHP_EOL);
            //get dictionary data and write file
            $tagId = (int) $currentTag[0];
            $dictionary = $termRepository->findByTagId($tagId);
            $this->session->set('test', $dictionary);
            $idx = 1;
            foreach ($dictionary as $d) {
                fwrite($txt, $idx . ': ' . $d->getWord() . ' - ' .
                    $d->getTranslation() . PHP_EOL);
                $idx++;
            }
            fclose($txt);
            //dl file
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' .
                basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            flush(); // Flush system output buffer
            readfile($file);
            unlink($file);
            die();
        }
        return $this->redirectToRoute('homepage_index');
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