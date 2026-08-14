<?php
/**
 * Outgoing mail.
 *
 * XAMPP on this machine has no sendmail_path and points SMTP at localhost:25,
 * where nothing is listening — so PHP's mail() fails silently and a password
 * reset would appear to work while sending nothing.
 *
 * The default driver therefore writes the full message to
 * storage/logs/mail.log, which makes reset links testable locally. Switch to
 * the 'mail' driver by setting MAIL_DRIVER in includes/config.local.php once a
 * real mail path exists.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Mailer
{
    public static function sendPasswordReset(array $user, string $token): bool
    {
        $link = rtrim(self::baseUrl(), '/') . url('reset-password/' . $token);

        $subject = t('auth.reset_title') . ' — ' . Setting::get('site_name', APP_NAME);
        $body    = t('auth.reset_intro') . "\n\n"
                 . $link . "\n\n"
                 . 'This link expires in ' . RESET_TOKEN_TTL_MINUTES . " minutes.\n";

        return self::send($user['email'], $subject, $body);
    }

    public static function send(string $to, string $subject, string $body): bool
    {
        $driver = defined('MAIL_DRIVER') ? MAIL_DRIVER : 'log';

        if ($driver === 'mail') {
            $headers = implode("\r\n", [
                'From: ' . (Setting::get('contact_email', 'no-reply@localhost')),
                'Content-Type: text/plain; charset=UTF-8',
                'MIME-Version: 1.0',
            ]);
            return @mail($to, $subject, $body, $headers);
        }

        return self::logMessage($to, $subject, $body);
    }

    /** Write the message to storage/logs/mail.log. */
    private static function logMessage(string $to, string $subject, string $body): bool
    {
        if (!is_dir(LOG_PATH)) {
            @mkdir(LOG_PATH, 0775, true);
        }

        $entry = sprintf(
            "=== %s ===\nTo: %s\nSubject: %s\n\n%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $body
        );

        return (bool) @file_put_contents(LOG_PATH . '/mail.log', $entry, FILE_APPEND | LOCK_EX);
    }

    /** Scheme + host, for building absolute links inside e-mail. */
    private static function baseUrl(): string
    {
        $scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
