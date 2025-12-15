<?php

namespace App\Services;

use App\Models\Payment_request;
use App\Models\user;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PayinFeeService
{
    /**
     * Recalculate and persist payin fees based on the gateway mode.
     */
    public static function syncModeFees(Payment_request $paymentRequest): void
    {
        if ((float) $paymentRequest->amount <= 0) {
            return;
        }

        $modeKey = self::detectModeKey($paymentRequest);

        if (!$modeKey) {
            return;
        }

        $percentage = self::resolveMerchantPercentage($paymentRequest->userid, $modeKey);

        if ($percentage === null) {
            return;
        }

        $baseCharge = round(((float) $paymentRequest->amount * $percentage) / 100, 4);
        $gst = round(($baseCharge * 18) / 100, 4);
        $calculatedTax = round($baseCharge + $gst, 2);

        if (round((float) $paymentRequest->tax, 2) === $calculatedTax) {
            return;
        }

        $paymentRequest->tax = $calculatedTax;
        $paymentRequest->saveQuietly();
    }

    protected static function detectModeKey(Payment_request $paymentRequest): ?string
    {
        $payload = self::normalizePayload($paymentRequest);
        $tenderInfo = Arr::get($payload, 'tenderInfo', []);

        $modeCandidates = [
            Arr::get($payload, 'mode'),
            Arr::get($payload, 'payment_source'),
            Arr::get($payload, 'payment_source_type'),
            Arr::get($payload, 'paymentMode'),
            Arr::get($payload, 'payment_mode'),
            Arr::get($payload, 'channel'),
            Arr::get($payload, 'channelType'),
            Arr::get($payload, 'processMethod'),
            Arr::get($tenderInfo, 'paymentMode'),
            Arr::get($tenderInfo, 'paymentType'),
            Arr::get($tenderInfo, 'cardType'),
            Arr::get($tenderInfo, 'cardCategory'),
            Arr::get($tenderInfo, 'cardBinCategory'),
            Arr::get($tenderInfo, 'cardBinIssuer'),
            Arr::get($tenderInfo, 'upiId'),
            $paymentRequest->data5,
        ];

        $cardCategory = self::stringify(
            Arr::get($payload, 'cardCategory')
            ?? Arr::get($payload, 'card_category')
            ?? Arr::get($payload, 'card_type')
            ?? Arr::get($payload, 'cardType')
            ?? Arr::get($tenderInfo, 'cardCategory')
            ?? Arr::get($tenderInfo, 'card_type')
            ?? Arr::get($tenderInfo, 'cardType')
            ?? Arr::get($tenderInfo, 'cardBinCategory')
        );

        // Detect card type (VISA, MASTER, RUPAY, etc.)
        $cardType = self::stringify(
            Arr::get($payload, 'cardType')
            ?? Arr::get($payload, 'card_type')
            ?? Arr::get($tenderInfo, 'cardType')
            ?? Arr::get($tenderInfo, 'card_type')
        );

        $isDebit = Str::contains($cardCategory, 'debit');
        $isCredit = Str::contains($cardCategory, 'credit');

        foreach ($modeCandidates as $candidate) {
            $key = self::mapModeToKey($candidate, $cardCategory, $payload, $cardType, $isDebit, $isCredit);
            if ($key) {
                return $key;
            }
        }

        if (!empty(self::stringify(
            Arr::get($payload, 'upi_va')
            ?? Arr::get($payload, 'vpa')
            ?? Arr::get($tenderInfo, 'upiId')
        ))) {
            return 'upi';
        }

        // If we have card type info, return specific key
        if (!empty($cardType) && ($isDebit || $isCredit)) {
            $cardTypeKey = self::normalizeCardType($cardType);
            if ($cardTypeKey) {
                $prefix = $isDebit ? 'dc' : 'cc';
                return $prefix . '_' . $cardTypeKey;
            }
        }

        if (!empty($cardCategory)) {
            return $isDebit ? 'dc' : 'cc';
        }

        return null;
    }

    protected static function mapModeToKey(?string $value, ?string $cardCategory, array $payload, ?string $cardType = null, bool $isDebit = false, bool $isCredit = false): ?string
    {
        $value = self::stringify($value);

        if ($value === '') {
            return null;
        }

        if (in_array($value, ['upi', 'qr', 'qrcode'], true)) {
            return 'upi';
        }

        if (in_array($value, ['dc', 'debit', 'debitcard'], true)) {
            // Check if we have card type info
            if (!empty($cardType)) {
                $cardTypeKey = self::normalizeCardType($cardType);
                if ($cardTypeKey) {
                    return 'dc_' . $cardTypeKey;
                }
            }
            return 'dc';
        }

        if (in_array($value, ['cc', 'credit', 'creditcard'], true)) {
            // Check if we have card type info
            if (!empty($cardType)) {
                $cardTypeKey = self::normalizeCardType($cardType);
                if ($cardTypeKey) {
                    return 'cc_' . $cardTypeKey;
                }
            }
            return 'cc';
        }

        if (in_array($value, ['nb', 'netbanking', 'netbank'], true)) {
            return 'nb';
        }

        if (Str::contains($value, 'upi') || Str::contains($value, 'qr')) {
            return 'upi';
        }

        if (Str::contains($value, 'net') || Str::contains($value, 'nb') || Str::contains($value, 'bank')) {
            return 'nb';
        }

        if (Str::contains($value, 'debit')) {
            if (!empty($cardType)) {
                $cardTypeKey = self::normalizeCardType($cardType);
                if ($cardTypeKey) {
                    return 'dc_' . $cardTypeKey;
                }
            }
            return 'dc';
        }

        if (Str::contains($value, 'credit')) {
            if (!empty($cardType)) {
                $cardTypeKey = self::normalizeCardType($cardType);
                if ($cardTypeKey) {
                    return 'cc_' . $cardTypeKey;
                }
            }
            return 'cc';
        }

        if (Str::contains($value, 'card')) {
            if (!empty($cardCategory)) {
                $isDebitCard = Str::contains($cardCategory, 'debit');
                if (!empty($cardType)) {
                    $cardTypeKey = self::normalizeCardType($cardType);
                    if ($cardTypeKey) {
                        return ($isDebitCard ? 'dc' : 'cc') . '_' . $cardTypeKey;
                    }
                }
                return $isDebitCard ? 'dc' : 'cc';
            }
            // Default to credit card if no category
            if (!empty($cardType)) {
                $cardTypeKey = self::normalizeCardType($cardType);
                if ($cardTypeKey) {
                    return 'cc_' . $cardTypeKey;
                }
            }
            return 'cc';
        }

        if (Str::contains($value, 'wallet')) {
            return 'upi';
        }

        if (Str::contains($value, 'imps') || Str::contains($value, 'rtgs')) {
            return 'nb';
        }

        $upiId = self::stringify(Arr::get($payload, 'upi_va') ?? Arr::get($payload, 'vpa'));
        if (!empty($upiId)) {
            return 'upi';
        }

        return null;
    }

    protected static function resolveMerchantPercentage(string $userid, string $modeKey): ?float
    {
        /** @var user|null $user */
        $user = user::where('userid', $userid)->first();

        if (!$user) {
            return null;
        }

        // Card type-specific percentages (most specific)
        $cardTypeSpecificMap = [
            'cc_master' => $user->cc_master_percentage,
            'cc_visa' => $user->cc_visa_percentage,
            'cc_rupay' => $user->cc_rupay_percentage,
            'cc_maestro' => $user->cc_maestro_percentage,
            'cc_amex' => $user->cc_amex_percentage,
            'cc_diners' => $user->cc_diners_percentage,
            'cc_others' => $user->cc_others_percentage,
            'dc_master' => $user->dc_master_percentage,
            'dc_visa' => $user->dc_visa_percentage,
            'dc_rupay' => $user->dc_rupay_percentage,
            'dc_maestro' => $user->dc_maestro_percentage,
            'dc_amex' => $user->dc_amex_percentage,
            'dc_diners' => $user->dc_diners_percentage,
            'dc_others' => $user->dc_others_percentage,
        ];

        // Card category percentages (fallback)
        $cardCategoryMap = [
            'cc' => $user->cc_percentage ?? $user->card_percentage,
            'dc' => $user->dc_percentage ?? $user->card_percentage,
        ];

        // Other mode percentages
        $otherModeMap = [
            'upi' => $user->upi_percentage,
            'nb' => $user->nb_percentage,
        ];

        // Try card type-specific first
        if (isset($cardTypeSpecificMap[$modeKey])) {
            $percentage = $cardTypeSpecificMap[$modeKey];
            if ($percentage !== null && $percentage !== '') {
                return (float) $percentage;
            }
            // Fallback to card category if type-specific is empty
            $categoryKey = Str::startsWith($modeKey, 'cc_') ? 'cc' : (Str::startsWith($modeKey, 'dc_') ? 'dc' : null);
            if ($categoryKey && isset($cardCategoryMap[$categoryKey])) {
                $percentage = $cardCategoryMap[$categoryKey];
                if ($percentage !== null && $percentage !== '') {
                    return (float) $percentage;
                }
            }
        }

        // Try card category
        if (isset($cardCategoryMap[$modeKey])) {
            $percentage = $cardCategoryMap[$modeKey];
            if ($percentage !== null && $percentage !== '') {
                return (float) $percentage;
            }
        }

        // Try other modes
        if (isset($otherModeMap[$modeKey])) {
            $percentage = $otherModeMap[$modeKey];
            if ($percentage !== null && $percentage !== '') {
                return (float) $percentage;
            }
        }

        // Final fallback to default percentage
        return $user->percentage !== null ? (float) $user->percentage : null;
    }

    /**
     * Normalize card type to key (master, visa, rupay, maestro, amex, diners, others)
     */
    protected static function normalizeCardType(?string $cardType): ?string
    {
        $normalized = self::stringify($cardType);

        if (empty($normalized)) {
            return null;
        }

        // Check in order of specificity
        if (Str::contains($normalized, 'mastercard') || Str::contains($normalized, 'master')) {
            return 'master';
        }

        if (Str::contains($normalized, 'visa')) {
            return 'visa';
        }

        if (Str::contains($normalized, 'rupay')) {
            return 'rupay';
        }

        if (Str::contains($normalized, 'maestro')) {
            return 'maestro';
        }

        if (Str::contains($normalized, 'amex') || Str::contains($normalized, 'american express')) {
            return 'amex';
        }

        if (Str::contains($normalized, 'diners') || Str::contains($normalized, 'diners club')) {
            return 'diners';
        }

        // For any other card type, return 'others'
        return 'others';
    }

    protected static function normalizePayload(Payment_request $paymentRequest): array
    {
        $payload = $paymentRequest->callback_payload;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return [];
        }

        if (is_array($payload)) {
            return $payload;
        }

        return [];
    }

    protected static function stringify($value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = Str::lower(trim((string) $value));

        if ($normalized === '' || in_array($normalized, ['na', 'n/a', 'notavailable', 'none', 'null', '-', '--'], true)) {
            return '';
        }

        return $normalized;
    }
}


        $user = user::where('userid', $userid)->first();

        if (!$user) {
            return null;
        }

        // Card type-specific percentages (most specific)
        $cardTypeSpecificMap = [
            'cc_master' => $user->cc_master_percentage,
            'cc_visa' => $user->cc_visa_percentage,
            'cc_rupay' => $user->cc_rupay_percentage,
            'cc_maestro' => $user->cc_maestro_percentage,
            'cc_amex' => $user->cc_amex_percentage,
            'cc_diners' => $user->cc_diners_percentage,
            'cc_others' => $user->cc_others_percentage,
            'dc_master' => $user->dc_master_percentage,
            'dc_visa' => $user->dc_visa_percentage,
            'dc_rupay' => $user->dc_rupay_percentage,
            'dc_maestro' => $user->dc_maestro_percentage,
            'dc_amex' => $user->dc_amex_percentage,
            'dc_diners' => $user->dc_diners_percentage,
            'dc_others' => $user->dc_others_percentage,
        ];

        // Card category percentages (fallback)
        $cardCategoryMap = [
            'cc' => $user->cc_percentage ?? $user->card_percentage,
            'dc' => $user->dc_percentage ?? $user->card_percentage,
        ];

        // Other mode percentages
        $otherModeMap = [
            'upi' => $user->upi_percentage,
            'nb' => $user->nb_percentage,
        ];

        // Try card type-specific first
        if (isset($cardTypeSpecificMap[$modeKey])) {
            $percentage = $cardTypeSpecificMap[$modeKey];
            if ($percentage !== null && $percentage !== '') {
                return (float) $percentage;
            }
            // Fallback to card category if type-specific is empty
            $categoryKey = Str::startsWith($modeKey, 'cc_') ? 'cc' : (Str::startsWith($modeKey, 'dc_') ? 'dc' : null);
            if ($categoryKey && isset($cardCategoryMap[$categoryKey])) {
                $percentage = $cardCategoryMap[$categoryKey];
                if ($percentage !== null && $percentage !== '') {
                    return (float) $percentage;
                }
            }
        }

        // Try card category
        if (isset($cardCategoryMap[$modeKey])) {
            $percentage = $cardCategoryMap[$modeKey];
            if ($percentage !== null && $percentage !== '') {
                return (float) $percentage;
            }
        }

        // Try other modes
        if (isset($otherModeMap[$modeKey])) {
            $percentage = $otherModeMap[$modeKey];
            if ($percentage !== null && $percentage !== '') {
                return (float) $percentage;
            }
        }

        // Final fallback to default percentage
        return $user->percentage !== null ? (float) $user->percentage : null;
    }

    /**
     * Normalize card type to key (master, visa, rupay, maestro, amex, diners, others)
     */
    protected static function normalizeCardType(?string $cardType): ?string
    {
        $normalized = self::stringify($cardType);

        if (empty($normalized)) {
            return null;
        }

        // Check in order of specificity
        if (Str::contains($normalized, 'mastercard') || Str::contains($normalized, 'master')) {
            return 'master';
        }

        if (Str::contains($normalized, 'visa')) {
            return 'visa';
        }

        if (Str::contains($normalized, 'rupay')) {
            return 'rupay';
        }

        if (Str::contains($normalized, 'maestro')) {
            return 'maestro';
        }

        if (Str::contains($normalized, 'amex') || Str::contains($normalized, 'american express')) {
            return 'amex';
        }

        if (Str::contains($normalized, 'diners') || Str::contains($normalized, 'diners club')) {
            return 'diners';
        }

        // For any other card type, return 'others'
        return 'others';
    }

    protected static function normalizePayload(Payment_request $paymentRequest): array
    {
        $payload = $paymentRequest->callback_payload;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return [];
        }

        if (is_array($payload)) {
            return $payload;
        }

        return [];
    }

    protected static function stringify($value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = Str::lower(trim((string) $value));

        if ($normalized === '' || in_array($normalized, ['na', 'n/a', 'notavailable', 'none', 'null', '-', '--'], true)) {
            return '';
        }

        return $normalized;
    }
}

