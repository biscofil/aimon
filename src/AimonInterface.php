<?php

namespace Aimon;

use GuzzleHttp\Client;
use Normalizer;

/**
 * Class AimonInterface
 * documentation:
 * http://sms.aimon.it/documentazione_api/Documentazione_BCP_API.pdf
 */
class AimonInterface
{

    private $maxLength = 459;

    private $login;
    private $password;

    /**
     * AimonInterface constructor.
     * @param string $login
     * @param string $password
     */
    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Send standard SMS without sender customization
     * @param $number
     * @param $message
     * @throws AimonException
     */
    public function sendSmartSmsMessage($number, $message)
    {
        $this->makeRequest([
            'number' => $number,
            'message' => $message,
            'id_api' => 106
        ]);
    }

    /**
     * Send SMS PRO with sender customization
     * @param string|integer $number
     * @param string $message
     * @param string $sender
     * @return void
     * @throws AimonException
     */
    public function sendProSmsMessage($number, $message, $sender)
    {
        $this->makeRequest([
            'number' => $number,
            'message' => $message,
            'sender' => $sender,
            'id_api' => 59
        ]);
    }

    /**
     * @param array $data
     * @throws AimonException
     */
    private function makeRequest($data)
    {

        $message = $data['message'];
        $number = $data['number'];
        $sender = isset($data['sender']) ? $data['sender'] : null;
        $apiID = $data['id_api'];

        $number = $this->addPrefixIfMissing($number);

        // cut if too long
        $message = mb_strimwidth($message, 0, $this->maxLength, "...");

        // convert text in the right charset
        $message = $this->utf8_to_gsm0338($message);

        $client = new Client();

        $response = $client->post("https://secure.apisms.it/http/send_sms", [
            'query' => [
                'authlogin' => $this->login,
                'authpasswd' => $this->password,
                'sender' => base64_encode($sender),
                'body' => base64_encode($message),
                'destination' => $this->getFormattedNumber($number),
                'id_api' => $apiID
            ]
        ]);

        $responseBody = (string)$response->getBody();

        if (strpos($responseBody, 'SMS Queued') !== false) {
            // perfect
            return;
        }

        throw new AimonException(
            "Error while sending sms message to " . $this->getFormattedNumber($number) . " : " . $responseBody,
            $response->getStatusCode()
        );

    }

    /**
     * @param string|integer $number
     * @param string $prefix
     * @return string
     */
    public function addPrefixIfMissing($number, $prefix = '+39')
    {
        // remove spaces from the phone number
        $number = preg_replace('/\s+/', '', strval($number));

        if (preg_match('/^([0-9]{10})$/', $number)) {
            // number has no prefix

            // prepend prefix
            $number = $prefix . $number;

        }

        return $number;
    }

    /**
     * Clickatell returns the number formatted without the prefix "+" and without spaces
     * @param $number
     * @return string
     */
    public function getFormattedNumber($number)
    {

        // remove spaces
        $number = preg_replace('/\s+/', '', $number);

        // remove plus
        $number = preg_replace('/\+/', '', $number);

        return $number;
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function utf8_to_gsm0338($string)
    {
        $dict = array(
            '@' => "\x00", '£' => "\x01", '$' => "\x02", '¥' => "\x03", 'è' => "\x04", 'é' => "\x05", 'ù' => "\x06", 'ì' => "\x07", 'ò' => "\x08", 'Ç' => "\x09", 'Ø' => "\x0B", 'ø' => "\x0C", 'Å' => "\x0E", 'å' => "\x0F",
            'Δ' => "\x10", '_' => "\x11", 'Φ' => "\x12", 'Γ' => "\x13", 'Λ' => "\x14", 'Ω' => "\x15", 'Π' => "\x16", 'Ψ' => "\x17", 'Σ' => "\x18", 'Θ' => "\x19", 'Ξ' => "\x1A", 'Æ' => "\x1C", 'æ' => "\x1D", 'ß' => "\x1E", 'É' => "\x1F",
            // all \x2? removed
            // all \x3? removed
            // all \x4? removed
            'Ä' => "\x5B", 'Ö' => "\x5C", 'Ñ' => "\x5D", 'Ü' => "\x5E", '§' => "\x5F",
            '¿' => "\x60",
            'ä' => "\x7B", 'ö' => "\x7C", 'ñ' => "\x7D", 'ü' => "\x7E", 'à' => "\x7F",
            '^' => "\x1B\x14", '{' => "\x1B\x28", '}' => "\x1B\x29", '\\' => "\x1B\x2F", '[' => "\x1B\x3C", '~' => "\x1B\x3D", ']' => "\x1B\x3E", '|' => "\x1B\x40", '€' => "\x1B\x65"
        );
        $converted = strtr(preg_replace('/\p{Mn}/u', '', Normalizer::normalize($string, Normalizer::FORM_KD)), $dict);

        // Replace unconverted UTF-8 chars from codepages U+0080-U+07FF, U+0080-U+FFFF and U+010000-U+10FFFF with a single ?
        return preg_replace('/([\\xC0-\\xDF].)|([\\xE0-\\xEF]..)|([\\xF0-\\xFF]...)/m', '?', $converted);
    }

}