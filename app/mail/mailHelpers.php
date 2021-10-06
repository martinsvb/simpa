<?php

const CHARSET_ISO88591 = 'iso-8859-1';
const CHARSET_UTF8 = 'utf-8';

const ENCODING_BASE64 = 'base64';
const ENCODING_7BIT = '7bit';
const ENCODING_8BIT = '8bit';
const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

const STD_LINE_LENGTH = 76;

const EOL = "\r\n";

/**
 *  Set correct mail content encoding
 *
 *  @param string $mail
 */
function setEncoding($mail)
{
    return (preg_match('#[^\n]{990}#', $mail)
        ? ENCODING_QUOTED_PRINTABLE
        : (preg_match('#[\x80-\xFF]#', $mail) ? ENCODING_8BIT : ENCODING_7BIT));
}

/**
 * Return an RFC 822 formatted date.
 */
function rfcDate()
{
    date_default_timezone_set(@date_default_timezone_get());
    return date('D, j M Y H:i:s O');
}

/**
 * Encode a string in requested format.
 * Returns an empty string on failure.
 *
 * @param string $str      The text to encode
 * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
 *
 * @return string
 */
function encodeString($str, $encoding = ENCODING_BASE64)
{
    $encoded = '';
    switch (strtolower($encoding)) {
        case ENCODING_BASE64:
            $encoded = chunk_split(
                base64_encode($str),
                STD_LINE_LENGTH,
                EOL
            );
            break;
        case ENCODING_7BIT:
        case ENCODING_8BIT:
            $encoded = normalizeBreaks($str);
            // Make sure it ends with a line break
            if (substr($encoded, -(strlen(EOL))) != EOL) {
                $encoded .= EOL;
            }
            break;
          case ENCODING_QUOTED_PRINTABLE:
            $encoded = encodeQP($str);
            break;
        default:
            $encoded = $str;
            break;
    }

    return $encoded;
}

/**
 * Encode a string in quoted-printable format.
 * According to RFC2045 section 6.7.
 *
 * @param string $string The text to encode
 *
 * @return string
 */
function encodeQP($string)
{
    return normalizeBreaks(quoted_printable_encode($string));
}

/**
 * Normalize line breaks in a string.
 * Converts UNIX LF, Mac CR and Windows CRLF line breaks into a single line break format.
 * Defaults to CRLF (for message bodies) and preserves consecutive breaks.
 *
 * @param string $text
 * @param string $breaktype What kind of line break to use; defaults to static::EOL
 *
 * @return string
 */
function normalizeBreaks($text, $breaktype = null)
{
    if (null === $breaktype) {
        $breaktype = EOL;
    }
    // Normalise to \n
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    // Now convert LE as needed
    if ("\n" !== $breaktype) {
        $text = str_replace("\n", $breaktype, $text);
    }

    return $text;
}
