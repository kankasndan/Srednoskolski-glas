<?php

namespace App\Support\Moderation;

/**
 * Provider-agnostic outcome of screening a file or a piece of text.
 */
final class ModerationVerdict
{
    /**
     * @param  string|null  $reason  Short English description for the logs. Never shown to the author.
     * @param  list<string>  $categories  Provider labels that triggered the decision.
     * @param  list<string>  $fields  Text fields that failed (`title`, `description`, `poll`, `content`).
     */
    public function __construct(
        public readonly ModerationDecision $decision,
        public readonly ?string $reason = null,
        public readonly array $categories = [],
        public readonly array $fields = [],
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
