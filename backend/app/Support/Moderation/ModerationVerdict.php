<?php

namespace App\Support\Moderation;

/**
 * Provider-agnostic outcome of screening a single file.
 */
final class ModerationVerdict
{
    /**
     * @param  string|null  $reason  Short English description for the logs. Never shown to the uploader.
     * @param  list<string>  $categories  Provider labels that triggered the decision.
     */
    public function __construct(
        public readonly ModerationDecision $decision,
        public readonly ?string $reason = null,
        public readonly array $categories = [],
    ) {}

    public static function allow(?string $reason = null): self
    {
        return new self(ModerationDecision::Allow, $reason);
    }

    /**
     * @param  list<string>  $categories
     */
    public static function escalate(string $reason, array $categories = []): self
    {
        return new self(ModerationDecision::Escalate, $reason, $categories);
    }

    public function isAllowed(): bool
    {
        return $this->decision === ModerationDecision::Allow;
    }
}
