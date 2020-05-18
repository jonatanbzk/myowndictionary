<?php


namespace App\Tests\Controller;


use App\Controller\Test\TestController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class TestControllerTest extends TestCase
{

    public function quizzMock($testLength, $testUserResponse, $testArray)
    {
        $mock = $this->getMockBuilder(TestController::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        return $mock->testScore($testLength, $testUserResponse, $testArray);
    }

    public function testTestScore1()
    {
        $testLength = 1;
        $testUserResponse = ['EEEE'];
        $testArray = [array(
            "words" => "EEEE",
            "translations" => "AAAA",
            "direction" => 2,
        )];
        $testResult = $this->quizzMock($testLength, $testUserResponse,
            $testArray);

        $this->assertEquals(1, $testResult[0]);
    }

    public function testTestScore2()
    {
        $testLength = 1;
        $testUserResponse = ['bbbb'];
        $testArray = [array(
            "words" => "aaaa",
            "translations" => "bbbb cccc",
            "direction" => 1,
        )];
        $testResult = $this->quizzMock($testLength, $testUserResponse,
            $testArray);

        $this->assertEquals(1, $testResult[0]);
    }

    public function testTestScore3()
    {
        $testLength = 1;
        $testUserResponse = ['cccc'];
        $testArray = [array(
            "words" => "aaaa",
            "translations" => "bbbb cccc",
            "direction" => 1,
        )];
        $testResult = $this->quizzMock($testLength, $testUserResponse,
            $testArray);

        $this->assertEquals(1, $testResult[0]);
    }

    public function testTestScore4()
    {
        $testLength = 1;
        $testUserResponse = ['Dddd'];
        $testArray = [array(
            "words" => "aaaa, bbbb",
            "translations" => "cccc, dddd, eeee",
            "direction" => 1,
        )];
        $testResult = $this->quizzMock($testLength, $testUserResponse,
            $testArray);

        $this->assertEquals(1, $testResult[0]);
    }
}