<?php
/*
 * This file is part of the Hawk package.
 *
 * (c) Eurolink <info@eurolink.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eurolink\Hawk;

/**
 * Client class for common response types and errors.
 *
 * @author Eurolink <info@eurolink.co>
 */
class Error
{
    public function __construct($message = null, $statusCode = null, $data = null)
    {
        $this->message = $message;
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->headers = [];

        // first header is the status.
        $this->headers[] = Utils::getStatusHeader($statusCode);
        $this->headers['X-Message'] = $message;
    }

    public function output()
    {
        Utils::setHeaders($this->headers);
    }

    public static function badRequest($message, $data = null)
    {
        return new Error($message, 400, $data);
    }

    public static function unauthorized($message = null, $attributes = null)
    {
        $scheme = 'Hawk';
        #$message = ($message ? $message : 'Hawk authentication required');

        $wwwAuthenticate = $scheme;

        // append attributes to header.
        // it may contain a ts (timestamp) and
        // tsm (timestamp MAC) for adjustments.
        if ($attributes) {
            $keys = array_keys($attributes);
            $len = count($keys);

            for ($i = 0; $i < $len; $i += 1) {
                if ($i) {
                    $wwwAuthenticate .= ',';
                }

                $key = $keys[$i];

                // a value can be zero!
                $value = (isset($attributes[$key]) ? $attributes[$key] : '');
                $value = Utils::getEscapeHeaderAttribute($value);

                $wwwAuthenticate .= ' ' . $key . '="' . $value . '"';
            }
        }

        if ($message) {
            if ($attributes) {
                $wwwAuthenticate .= ',';
            }

            $wwwAuthenticate .= ' error="' . Utils::getEscapeHeaderAttribute($message) . '"';
        }

        $error = new Error($message, 401);
        $error->headers['WWW-Authenticate'] = $wwwAuthenticate;

        return $error;
    }

    // 5xx Server Errors
    public static function internal($message, $data = null)
    {
        return new Error($message, 500, $data);
    }
}