<?php

use App\Support\HtmlSanitizer;

it('strips script tags and event handlers from thread html', function () {
    $dirty = '<p>Hello</p><img src=x onerror="alert(1)"><script>alert(1)</script><a href="javascript:alert(1)">x</a>';

    $clean = HtmlSanitizer::clean($dirty);

    expect($clean)->toContain('<p>Hello</p>')
        ->and($clean)->not->toContain('<script')
        ->and($clean)->not->toContain('onerror')
        ->and($clean)->not->toContain('javascript:');
});

it('keeps safe tip tap markup', function () {
    $html = '<p>Hi <strong>there</strong></p><ul><li>one</li></ul><a href="https://example.com" target="_blank">link</a>';

    $clean = HtmlSanitizer::clean($html);

    expect($clean)->toContain('<strong>there</strong>')
        ->and($clean)->toContain('<li>one</li>')
        ->and($clean)->toContain('href="https://example.com"')
        ->and($clean)->toContain('rel="noopener noreferrer ugc"');
});

it('converts tip tap html to plain text without tags', function () {
    $html = '<p>Hello</p><p>world <strong>bold</strong></p>';

    expect(HtmlSanitizer::plainText($html))->toBe('Hello world bold');
});

it('rejects protocol-relative hrefs', function () {
    $clean = HtmlSanitizer::clean('<a href="//evil.example/phish">x</a>');

    expect($clean)->not->toContain('href="//evil.example/phish"')
        ->and($clean)->not->toContain('href="//');
});

it('drops script and style bodies instead of leaving their source as text', function () {
    expect(HtmlSanitizer::clean('<script>alert(1)</script>'))->toBe('')
        ->and(HtmlSanitizer::clean('<style>body{color:red}</style>'))->toBe('')
        ->and(HtmlSanitizer::plainText('<p>Hi</p><script>alert(1)</script>'))->toBe('Hi');
});

// A disallowed wrapper used to hoist its children after the loop had already
// snapshotted them, so anything one level down escaped sanitizing entirely.
it('sanitizes content nested inside disallowed wrappers', function (string $dirty) {
    $clean = HtmlSanitizer::clean($dirty);

    expect($clean)->not->toContain('<script')
        ->and($clean)->not->toContain('<iframe')
        ->and($clean)->not->toContain('onerror')
        ->and($clean)->not->toContain('onclick')
        ->and($clean)->not->toContain('javascript:');
})->with([
    '<div><script>alert(1)</script></div>',
    '<span><img src=x onerror=alert(1)></span>',
    '<section><iframe src="https://evil.example"></iframe></section>',
    '<div><a href="javascript:alert(1)">x</a></div>',
    '<div><p onclick="alert(1)">x</p></div>',
    '<svg><animate onbegin=alert(1) attributeName=x dur=1s></svg>',
    '<math><mtext><table><mglyph><style><img src=x onerror=alert(1)>',
    '<noscript><p title="</noscript><img src=x onerror=alert(1)>">',
    '<form><button formaction="javascript:alert(1)">go</button></form>',
    '<div><div><div><img src=x onerror=alert(1)></div></div></div>',
]);

it('keeps text content when unwrapping a harmless disallowed wrapper', function () {
    $clean = HtmlSanitizer::clean('<div><p>Hello <strong>world</strong></p></div>');

    expect($clean)->toContain('<p>Hello <strong>world</strong></p>');
});
