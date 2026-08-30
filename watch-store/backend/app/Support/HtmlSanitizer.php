<?php

namespace App\Support;

/**
 * Minimal allowlist-based HTML sanitizer for admin-authored CMS/FAQ content.
 *
 * No third-party dependency is available in this environment, so this uses
 * DOMDocument with a strict tag/attribute allowlist rather than a regex
 * strip (regex HTML parsing is unreliable and easy to bypass). Anything not
 * on the allowlist is dropped; script and iframe tags, event handler
 * attributes, and javascript: links are always removed.
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'a', 'span', 'div', 'blockquote',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'hr',
    ];

    private const ALLOWED_ATTRS = [
        'a' => ['href', 'title', 'target', 'rel'],
    ];

    public static function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="utf-8" ?><div id="__root__">' . $html . '</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $root = $doc->getElementById('__root__');
        if (! $root) {
            return '';
        }

        self::sanitizeNode($doc, $root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function sanitizeNode(\DOMDocument $doc, \DOMNode $node): void
    {
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }

            if (! $child instanceof \DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unwrap disallowed tags: keep their text/children, drop the tag itself.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::sanitizeAttributes($child, $tag);
            self::sanitizeNode($doc, $child);
        }
    }

    private static function sanitizeAttributes(\DOMElement $el, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRS[$tag] ?? [];
        $attrs = iterator_to_array($el->attributes ?? []);

        foreach ($attrs as $attr) {
            $name = strtolower($attr->name);

            if (str_starts_with($name, 'on') || ! in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->name);
                continue;
            }

            if ($name === 'href') {
                $value = trim($attr->value);
                if (preg_match('/^\s*javascript:/i', $value) || preg_match('/^\s*data:/i', $value)) {
                    $el->removeAttribute('href');
                }
            }
        }

        if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }
}
