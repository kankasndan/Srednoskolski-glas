<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allowlist sanitizer for TipTap thread HTML stored in `threads.description`.
 */
class HtmlSanitizer
{
    /** @var list<string> */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3',
        'blockquote', 'code', 'pre',
    ];

    /**
     * Removed with their whole subtree. Their content is never renderable text,
     * so unwrapping them would leak script bodies or re-parsed markup.
     *
     * @var list<string>
     */
    private const DROPPED_TAGS = [
        'script', 'style', 'template', 'noscript', 'iframe', 'object', 'embed',
        'svg', 'math', 'head', 'meta', 'link', 'base', 'title',
    ];

    /** @var array<string, list<string>> */
    private const ALLOWED_ATTRS = [
        'a' => ['href', 'target', 'rel'],
    ];

    /**
     * TipTap HTML → plain text for admin previews (no visible tags).
     */
    public static function plainText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Drop script/style bodies so their source never shows up as preview text.
        $html = preg_replace('/<\s*(script|style)\b[^>]*>.*?<\s*\/\s*\1\s*>/isu', ' ', $html) ?? $html;

        // Turn block/break tags into spaces so adjacent paragraphs don't glue together.
        $withBreaks = preg_replace('/<\s*\/?\s*(p|div|br|li|h[1-6]|blockquote|tr)\b[^>]*>/iu', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    public static function clean(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $wrapped = '<div id="sg-sanitize-root">'.$html.'</div>';
        $document->loadHTML(
            '<?xml encoding="UTF-8">'.$wrapped,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('sg-sanitize-root');

        if ($root === null) {
            return '';
        }

        self::sanitizeNode($root);

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        if (! $node->hasChildNodes()) {
            return;
        }

        /** @var list<DOMNode> $children */
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE || ! $child instanceof DOMElement) {
                $child->parentNode?->removeChild($child);

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROPPED_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Keep children of disallowed wrappers (e.g. <div>, <span>), but
                // clean the subtree *before* hoisting it: the loop iterates over a
                // snapshot of $node's children, so anything moved in afterwards
                // would never be visited again.
                self::sanitizeNode($child);

                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::sanitizeAttributes($child, $tag);
            self::sanitizeNode($child);
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRS[$tag] ?? [];

        /** @var list<string> $names */
        $names = [];
        foreach ($element->attributes ?? [] as $attribute) {
            $names[] = $attribute->name;
        }

        foreach ($names as $name) {
            $lower = strtolower($name);

            if (! in_array($lower, $allowed, true)) {
                $element->removeAttribute($name);

                continue;
            }

            if ($lower === 'href') {
                $href = trim((string) $element->getAttribute('href'));
                if (! self::isSafeHref($href)) {
                    $element->removeAttribute('href');
                } else {
                    $element->setAttribute('href', $href);
                    $element->setAttribute('rel', 'noopener noreferrer ugc');
                    if ($element->getAttribute('target') === '_blank') {
                        $element->setAttribute('target', '_blank');
                    } else {
                        $element->removeAttribute('target');
                    }
                }
            }
        }
    }

    private static function isSafeHref(string $href): bool
    {
        if ($href === '' || str_starts_with($href, '#')) {
            return true;
        }

        if (preg_match('/^\s*(javascript|data|vbscript):/i', $href) === 1) {
            return false;
        }

        if (str_starts_with($href, '//')) {
            return false;
        }

        return (bool) preg_match('/^(https?:\/\/|mailto:|\/[^\/])/i', $href);
    }
}
