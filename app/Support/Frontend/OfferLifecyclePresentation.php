<?php

namespace App\Support\Frontend;

/**
 * Frontend-only offer lifecycle presentation (F9D / WF4 / ERD offers).
 * ERD statuses: sent | accepted | rejected | expired (+ FE draft/approval extensions).
 */
class OfferLifecyclePresentation
{
    public const DRAFT = 'draft';

    public const PENDING_APPROVAL = 'pending_approval';

    public const SENT = 'sent';

    public const AWAITING = 'awaiting';

    public const ACCEPTED = 'accepted';

    public const DECLINED = 'declined';

    public const EXPIRED = 'expired';

    public const WITHDRAWN = 'withdrawn';

    /**
     * @param  array<string, mixed>  $offer
     * @return array<string, mixed>
     */
    public static function enrich(array $offer): array
    {
        $status = $offer['status_key'] ?? self::AWAITING;
        $canRespond = in_array($status, [self::AWAITING, self::SENT], true);

        $offer['status_key'] = $status;
        $offer['can_accept'] = $canRespond;
        $offer['can_decline'] = $canRespond;
        $offer['response_blocked_reason'] = $canRespond
            ? null
            : match ($status) {
                self::EXPIRED => __('candidate.offers.blocked_expired'),
                self::ACCEPTED => __('candidate.offers.blocked_accepted'),
                self::DECLINED => __('candidate.offers.blocked_declined'),
                self::WITHDRAWN => __('candidate.offers.blocked_withdrawn'),
                default => __('candidate.offers.blocked_unavailable'),
            };
        $offer['currency'] = $offer['currency'] ?? 'USD';
        $offer['currency_note'] = $offer['currency_note'] ?? __('candidate.offers.currency_note');
        $offer['is_sample'] = true;
        $offer['decision_authority_note'] = __('candidate.offers.decision_authority');

        return $offer;
    }

    /**
     * Badge variant for offer status_key.
     */
    public static function badgeVariant(string $statusKey): string
    {
        return match ($statusKey) {
            self::ACCEPTED => 'success',
            self::DECLINED, self::WITHDRAWN => 'neutral',
            self::EXPIRED => 'pending',
            self::DRAFT, self::PENDING_APPROVAL => 'info',
            default => 'pending',
        };
    }
}
