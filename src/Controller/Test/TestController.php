<?php


namespace App\Controller\Test;

use App\Repository\TermRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function index()
    {
        return $this->render('test/testForm.html.twig');
    }

    /**
     * @Route("runTest", name="runTest")
     * @param Request $request
     * @param TermRepository $termRepository
     * @return Response
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

        $testDataArray = array();
        $testData =$termRepository->findForTest($tagId, $testLength);
        foreach ($testData as $td) {
            if ($testDirection == 3) {
                $direction = random_int(1, 2);
            } else {
                $direction = $testDirection;
            }
            $testOneLine = array(
                "words" => $td->getWord(),
                "translations" => $td->getTranslation(),
                "direction" => $direction
            );
            array_push($testDataArray, $testOneLine);
        }
        $this->session->set('testArray', $testDataArray);
        return $this->render('test/test.html.twig', [
            'testArray' => $testDataArray,
        ]);
    }

    /**
     * @Route("processingTest", name="processingTest")
     * @param Request $request
     * @return Response
     */
    public function processingTest(Request $request)
    {
        $testArray = $this->session->get('testArray');
        $testLength = count($testArray);
        $testUserResponse = [];
        $score = 0;
        $resultArray = array();
        for ($i = 0; $i < $testLength; $i++) {
            array_push($testUserResponse, $request->get(
                'response' . $i) );
            }
        for ($i = 0; $i < $testLength; $i++) {
            $rep = trim(strtolower($testUserResponse[$i]));
            $rgx = '/^' . $rep . '$|^' . $rep . '[^a-z]|[^a-z]' . $rep .
                '[^a-z]|[^a-z]' . $rep . '$/';
            if ($testArray[$i]["direction"] == 1 &&
            preg_match($rgx, $testArray[$i]["translations"])) {
                $score++;
                $result = array(
                    "result" => 1,    // 1 = good answer
                    "term1" => $testArray[$i]["words"],  // question
                    "term2" => $testUserResponse[$i],    // user answer
                );
                array_push($resultArray, $result);
            } elseif ($testArray[$i]["direction"] == 2 &&
                preg_match($rgx ,$testArray[$i]["words"])) {
                $score++;
                $result = array(
                    "result" => 1,
                    "term1" => $testArray[$i]["translations"],
                    "term2" => $testUserResponse[$i],
                );
                array_push($resultArray, $result);
            } else {         // bad user answer
                if ($testArray[$i]["direction"] == 1) {
                    $result = array(
                        "result" => 0,   // 0 = bad answer
                        "term1" => $testArray[$i]["words"],
                        "term2" => $testUserResponse[$i],
                        "term3" => $testArray[$i]["translations"], //good answer
                    );
                    array_push($resultArray, $result);
                }
                if ($testArray[$i]["direction"] == 2) {
                    $result = array(
                        "result" => 0,
                        "term1" => $testArray[$i]["translations"],
                        "term2" => $testUserResponse[$i],
                        "term3" => $testArray[$i]["words"],
                    );
                    array_push($resultArray, $result);
                }
            }
        }
        $scoreRate = $score / $testLength;
        $this->get('session')->remove('testArray');
        return $this->render('test/testResult.html.twig', [
            'score' => $score,
            'scoreRate' => $scoreRate,
            'testLength' => $testLength,
            'testResult' => $resultArray,
        ]);
    }

}