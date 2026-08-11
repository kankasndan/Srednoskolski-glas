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
