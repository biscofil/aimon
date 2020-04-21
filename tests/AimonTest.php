<?php

use Aimon\AimonInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class EmailTest
 */
class AimonTest extends TestCase
{

    /**
     * @test
     */
    public function addPrefixIfMissingTest()
    {

        $aimon = new AimonInterface("login", "password");

        $numberPrefix = "+39";
        $numberWithoutPrefix = "0000000000";
        $numberWithPrefix = $numberPrefix . $numberWithoutPrefix;

        $formattedNumber = $aimon->addPrefixIfMissing($numberWithPrefix, $numberPrefix);
        $this->assertEquals($numberWithPrefix, $formattedNumber);

        $formattedNumber = $aimon->addPrefixIfMissing($numberWithoutPrefix, $numberPrefix);
        $this->assertEquals($numberWithPrefix, $formattedNumber);

    }

    /**
     * @test
     */
    public function getFormattedNumberTest()
    {

        $aimon = new AimonInterface("login", "password");

        $formattedNumber = $aimon->getFormattedNumber("+390000000000");
        $this->assertEquals("390000000000", $formattedNumber);

    }

}
