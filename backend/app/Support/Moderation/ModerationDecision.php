<?php

namespace App\Support\Moderation;

enum ModerationDecision: string
{
    /** The file may be published. */
    case Allow = 'allow';

    /** The file breaks the content policy and must not be stored. */
    case Reject = 'reject';

    /**
     * The file may involve sexual content of a minor. Treated like a rejection
     * for the uploader, but logged loudly because it needs a human decision and,
     * depending on jurisdiction, a report to the authorities.
     */
    case Escalate = 'escalate';
}
