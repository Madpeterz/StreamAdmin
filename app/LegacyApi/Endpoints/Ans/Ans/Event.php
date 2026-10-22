<?php

namespace App\LegacyApi\Endpoints\Ans\Ans;

use App\LegacyApi\Endpoint;
use App\LegacyApi\SlEndpoint;
use App\Models\MarketplaceCoupon;
use App\Models\Transaction;
use App\Services\BotService;
use App\Support\Uid;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Linden Marketplace Automated Notification: a coupon listing was bought, so credit the
 * receiver's account. Verified with sha1(query string . ANS salt); the unique SL transaction id
 * stops replays.
 */
class Event extends Endpoint
{
    public function process(): void
    {
        $config = $this->slConfig();
        if (! $config->enable_coupons) {
            $this->failed('Failed ANS disabled');

            return;
        }
        $checkHash = $this->request->header('X-ANS-Verify-Hash');
        if ($checkHash === null) {
            $this->failed('VERIFY_HASH has not been set on the system');

            return;
        }
        if ($checkHash === '') {
            $this->failed('VERIFY_HASH is empty');

            return;
        }
        $fields = [
            'PaymentGross' => $this->input->int('PaymentGross'),
            'payerKey' => $this->input->uuid('PayerKey'),
            'payerName' => $this->input->string('PayerName'),
            'receiverKey' => $this->input->uuid('ReceiverKey'),
            'receiverName' => $this->input->string('ReceiverName'),
            'transactionId' => $this->input->stringLength('TransactionID', 3, 20),
            'itemId' => $this->input->stringLength('ItemID', 3, 10) === null ? null : $this->input->int('ItemID'),
        ];
        foreach ($fields as $name => $value) {
            // 2.0.x checked with in_array(null, ...), so 0 and "" count as missing too
            if ($value === null || $value === 0 || $value === '') {
                $this->failed('ANS not accepted missing value for: '.$name);

                return;
            }
        }
        if ($this->input->string('Type') !== 'Purchase') {
            $this->failed('ANS redelivery so ignored');

            return;
        }
        $coupon = MarketplaceCoupon::where('listing_id', $fields['itemId'])->first();
        if ($coupon === null) {
            $this->failed('not a tracked listing');

            return;
        }
        if ((int) $coupon->cost !== $fields['PaymentGross']) {
            $this->failed('amount paid does not match expected to process');

            return;
        }
        if (! hash_equals(sha1((string) $this->request->server('QUERY_STRING').$config->ans_salt), $checkHash)) {
            $this->failed('Unable to vaildate');

            return;
        }
        $coupon->update(['claims' => $coupon->claims + 1, 'last_claim' => time()]);
        $payer = SlEndpoint::avatarFor($fields['payerKey'], self::fullName($fields['payerName']));
        if ($payer === null) {
            $this->failed('Unable to create/load payer avatar');

            return;
        }
        $receiver = SlEndpoint::avatarFor($fields['receiverKey'], self::fullName($fields['receiverName']));
        if ($receiver === null) {
            $this->failed('Unable to create/load receiver avatar');

            return;
        }
        try {
            Transaction::create([
                'avatar_id' => $payer->id,
                'amount' => $fields['PaymentGross'],
                'unixtime' => time(),
                'transaction_uid' => Uid::unique(Transaction::class, 'transaction_uid'),
                'sl_transaction_uuid' => $fields['transactionId'],
                'via_marketplace' => true,
                'notes' => 'Coupon L$'.$fields['PaymentGross'].' added L$'.$coupon->credit,
                'target_avatar_id' => $receiver->id !== $payer->id ? $receiver->id : null,
            ]);
        } catch (UniqueConstraintViolationException) {
            $this->set('message', 'Unable to create transaction');

            return;
        }
        $bot = app(BotService::class);
        $bot->sendMessage($receiver, 'Hi there '.$receiver->avatar_name.' a credit of L$'.$coupon->credit.' has been added to your account!');
        if ($receiver->id !== $payer->id) {
            $bot->sendMessage($payer, 'Hi there '.$payer->avatar_name.' thank you for adding L$'.$coupon->credit.' to the account for '.$receiver->avatar_name);
        }
        $receiver->update(['credits' => $receiver->credits + $coupon->credit]);
        $this->ok('proceed ans');
    }

    private static function fullName(string $name): string
    {
        return count(explode(' ', $name)) === 1 ? $name.' Resident' : $name;
    }
}
