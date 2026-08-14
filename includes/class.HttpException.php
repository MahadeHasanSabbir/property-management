<?php
/**
 * An exception carrying an HTTP status. Thrown by abort() and by the router,
 * caught by the global handler in bootstrap.php, which renders the matching
 * error view.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

class HttpException extends \RuntimeException
{
    private int $statusCode;
    private string $viewKey;

    /**
     * @param int    $statusCode must be a real HTTP status. Apache rewrites
     *                           codes it does not recognise (a 419, for
     *                           instance) into a bare 500, so inventing one
     *                           silently loses the meaning.
     * @param string $viewKey    which error copy to show, when it differs from
     *                           the status — a failed CSRF check answers 403
     *                           but reads better as "session expired".
     */
    public function __construct(int $statusCode, string $message = '', string $viewKey = '')
    {
        $this->statusCode = $statusCode;
        $this->viewKey    = $viewKey !== '' ? $viewKey : (string) $statusCode;
        parent::__construct($message !== '' ? $message : self::defaultMessage($statusCode));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** Lang-key prefix for the error page copy. */
    public function getViewKey(): string
    {
        return $this->viewKey;
    }

    private static function defaultMessage(int $status): string
    {
        return [
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Session expired',
            429 => 'Too Many Requests',
        ][$status] ?? 'Server Error';
    }
}
