<?php

namespace App\Services;

class ShortcodeProcessor
{
    /**
     * Process WordPress shortcodes in content.
     *
     * Handles the top 4 shortcode types found in the WordPress export:
     * - [more] - Read more break marker
     * - [caption] - Image captions with figure/figcaption
     * - [video] - HTML5 video elements
     * - [gravityform] - Form placeholders with contact link
     *
     * Unknown shortcodes are stripped to prevent raw [shortcode] in output.
     */
    public function process(string $content): string
    {
        // [more] - Convert to horizontal rule (read more break)
        $content = preg_replace('/\[more\]/', '<hr class="wp-more">', $content);

        // [caption] - Convert to figure/figcaption
        $content = preg_replace_callback(
            '/\[caption[^\]]*\](.*?)\[\/caption\]/s',
            fn($m) => $this->processCaption($m[0], $m[1]),
            $content
        );

        // [video] - Convert to HTML5 video element
        $content = preg_replace_callback(
            '/\[video([^\]]*)\]/s',
            fn($m) => $this->processVideo($m[1]),
            $content
        );

        // [gravityform] - Replace with placeholder or contact link
        $content = preg_replace(
            '/\[gravityform[^\]]*\]/',
            '<div class="bg-gray-100 p-4 rounded text-center">
                <p>Form temporarily unavailable. <a href="/contact/" class="text-blue-600">Contact us</a></p>
            </div>',
            $content
        );

        // Strip any remaining unknown shortcodes (fallback)
        $content = preg_replace('/\[[^\]]+\]/', '', $content);

        return $content;
    }

    /**
     * Process [caption] shortcode to figure/figcaption.
     *
     * Handles attributes in any order: width, align, caption, id.
     */
    private function processCaption(string $full, string $inner): string
    {
        // Extract attributes - handles them in any order
        preg_match('/width="(\d+)"/', $full, $width);
        preg_match('/caption="([^"]*)"/', $full, $caption);
        preg_match('/align="([^"]*)"/', $full, $align);

        $w = $width[1] ?? 'auto';
        $cap = $caption[1] ?? '';
        $alignClass = isset($align[1]) ? ' ' . $align[1] : '';

        return sprintf(
            '<figure class="wp-caption%s" style="max-width:%spx">%s<figcaption>%s</figcaption></figure>',
            $alignClass,
            $w,
            trim($inner),
            $cap ?: trim(strip_tags($inner))
        );
    }

    /**
     * Process [video] shortcode to HTML5 video element.
     *
     * Handles mp4, webm, and poster attributes.
     */
    private function processVideo(string $attrs): string
    {
        preg_match('/mp4="([^"]*)"/', $attrs, $mp4);
        preg_match('/webm="([^"]*)"/', $attrs, $webm);
        preg_match('/poster="([^"]*)"/', $attrs, $poster);

        $sources = '';
        if (!empty($mp4[1])) {
            $sources .= sprintf('<source src="%s" type="video/mp4">', htmlspecialchars($mp4[1]));
        }
        if (!empty($webm[1])) {
            $sources .= sprintf('<source src="%s" type="video/webm">', htmlspecialchars($webm[1]));
        }

        $posterAttr = !empty($poster[1]) ? 'poster="' . htmlspecialchars($poster[1]) . '"' : '';

        return sprintf(
            '<video class="wp-video" controls preload="metadata" %s>%s</video>',
            $posterAttr,
            $sources
        );
    }
}
