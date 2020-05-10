<?php


namespace App\Controller\Test;

use App\Repository\TermRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class TestController extends AbstractController
{

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/test", name="test")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render('test/testForm.html.twig');
    }

    /**
     * @Route("runTest", name="runTest")
     */
    public function runTest(Request $request, TermRepository $termRepository)
    {
        $currentTag = $this->session->get('current_tag');
        $tagId = (int) $currentTag[0];
        $testDirection = $request->get('testDirection');
        $testLength = $request->get('testLength');
        if ($testLength == null) {
            $testLength = 10;
        }
        $testData =$termRepository->findForTest($tagId, $testLength);

        $this->session->set('testDirection', $testDirection);
        $this->session->set('testLength', $testLength);
        $this->session->set('testData', $testData);
        return $this->render('test/test.html.twig');
    }

}