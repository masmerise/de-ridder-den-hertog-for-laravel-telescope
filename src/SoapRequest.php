<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

final readonly class SoapRequest
{
    /** @throws CouldNotParseSoapRequest */
    public static function unwrap(string $xml): array
    {
        $openingTagPosition = strrpos($xml, $openingTag = '<paramRequest>');
        if ($openingTagPosition === false) {
            throw CouldNotParseSoapRequest::openingTagCouldNotBeFound();
        }

        $closingTagPosition = strrpos($xml, '</paramRequest>');
        if ($closingTagPosition === false) {
            throw CouldNotParseSoapRequest::closingTagCouldNotBeFound();
        }

        $json = substr($xml, $openingTagPosition + strlen($openingTag), $closingTagPosition - $openingTagPosition - strlen($openingTag));
        $json = base64_decode($json);
        $json = json_decode($json, true);

        return $json ?? throw CouldNotParseSoapRequest::unknownError();
    }

    private function __construct() {}
}
