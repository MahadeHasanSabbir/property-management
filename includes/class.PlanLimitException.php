<?php
/**
 * Raised when an action exceeds the user's plan entitlement.
 *
 * Distinct from HttpException because it is not an error condition — it is an
 * expected outcome with a specific, translated message and an upgrade prompt.
 * Controllers catch it and turn it into a flash rather than an error page.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

class PlanLimitException extends \RuntimeException
{
}
