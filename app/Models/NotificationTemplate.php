<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'label', 'category', 'channel', 'subject', 'body', 'variables', 'is_active'];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get a template by key, with caching.
     */
    public static function getTemplate(string $key): ?self
    {
        return cache()->remember("notif_template:{$key}", 300, function () use ($key) {
            return static::where('key', $key)->where('is_active', true)->first();
        });
    }

    /**
     * Render the body with variable substitution.
     */
    public static function render(string $key, array $vars = []): ?string
    {
        $template = static::getTemplate($key);
        if (!$template) return null;

        return static::substitute($template->body, $vars);
    }

    /**
     * Render the subject with variable substitution (email only).
     */
    public static function renderSubject(string $key, array $vars = []): ?string
    {
        $template = static::getTemplate($key);
        if (!$template || !$template->subject) return null;

        return static::substitute($template->subject, $vars);
    }

    /**
     * Replace {{var}} placeholders with values.
     */
    public static function substitute(string $text, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value ?? '', $text);
        }
        return $text;
    }

    /**
     * Clear cache for a specific template.
     */
    public static function clearCache(string $key): void
    {
        cache()->forget("notif_template:{$key}");
    }
}
