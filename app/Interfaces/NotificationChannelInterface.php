<?php declare(strict_types=1);
namespace App\Interfaces;

/** Notification channel contract (Strategy Pattern). */
interface NotificationChannelInterface
{
    /**
     * Sends a notification through this channel.
     *
     * @param array<string, mixed> $recipient   User data array
     * @param string               $subject     Notification subject / title
     * @param string               $body        Rendered template body
     * @param array<string, mixed> $metadata    Channel-specific extra data
     */
    public function send(array $recipient, string $subject, string $body, array $metadata = []): bool;

    /** Returns the channel identifier (e.g. 'email', 'sms', 'in_app'). */
    public function getChannel(): string;

    /** Whether this channel is currently enabled and configured. */
    public function isAvailable(): bool;
}
