<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceCoupon;
use App\Models\SlConfig;
use App\Models\Transaction;
use App\Services\AvatarDirectory;
use App\Services\BotService;
use App\Support\Uid;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Linden Marketplace Automated Notification (ANS): a coupon listing was bought, so credit the
 * receiver's account. The caller is Linden Lab, so the protocol is theirs: a GET at /ans.php,
 * verified with sha1(raw query string . ANS salt) in X-ANS-Verify-Hash. The unique SL transaction
 * id stops replays. Always answers HTTP 200 with a JSON reply.
 */
class MarketplaceNotification extends Controller
{
    public function __invoke(Request $request, AvatarDirectory $avatars, BotService $bot): JsonResponse
    {
        $config = SlConfig::findOrFail(1);
        if (! $config->enable_coupons) {
            return $this->refuse('ANS is disabled');
        }
        $given = (string) $request->header('X-ANS-Verify-Hash');
        if ($given === '' || ! hash_equals(sha1((string) $request->server('QUERY_STRING').$config->ans_salt), $given)) {
            return $this->refuse('Unable to validate');
        }

        $validator = Validator::make($request->query(), [
            'Type' => ['required', 'in:Purchase'],
            'PaymentGross' => ['required', 'integer', 'min:1'],
            'PayerKey' => ['required', 'uuid'],
            'PayerName' => ['required', 'string', 'max:255'],
            'ReceiverKey' => ['required', 'uuid'],
            'ReceiverName' => ['required', 'string', 'max:255'],
            'TransactionID' => ['required', 'string', 'min:3', 'max:20'],
            'ItemID' => ['required', 'integer', 'min:1'],
        ]);
        if ($validator->fails()) {
            return $this->refuse($validator->errors()->first());
        }
        $fields = $validator->validated();
        $coupon = MarketplaceCoupon::where('listing_id', (int) $fields['ItemID'])->first();
        if ($coupon === null) {
            return $this->refuse('Not a tracked listing');
        }
        if ((int) $coupon->cost !== (int) $fields['PaymentGross']) {
            return $this->refuse('The amount paid does not match the listing');
        }

        try {
            DB::transaction(function () use ($fields, $coupon, $avatars, $bot) {
                $payer = $avatars->findOrCreate($fields['PayerKey'], $this->fullName($fields['PayerName']));
                $receiver = $avatars->findOrCreate($fields['ReceiverKey'], $this->fullName($fields['ReceiverName']));
                Transaction::create([
                    'avatar_id' => $payer->id,
                    'amount' => (int) $fields['PaymentGross'],
                    'unixtime' => time(),
                    'transaction_uid' => Uid::unique(Transaction::class, 'transaction_uid'),
                    'sl_transaction_uuid' => $fields['TransactionID'],
                    'via_marketplace' => true,
                    'notes' => 'Coupon L$'.$fields['PaymentGross'].' added L$'.$coupon->credit,
                    'target_avatar_id' => $receiver->id !== $payer->id ? $receiver->id : null,
                ]);
                $coupon->update(['claims' => $coupon->claims + 1, 'last_claim' => time()]);
                $receiver->update(['credits' => $receiver->credits + $coupon->credit]);
                $bot->sendMessage($receiver, 'Hi there '.$receiver->avatar_name.' a credit of L$'.$coupon->credit.' has been added to your account!');
                if ($receiver->id !== $payer->id) {
                    $bot->sendMessage($payer, 'Hi there '.$payer->avatar_name.' thank you for adding L$'.$coupon->credit.' to the account for '.$receiver->avatar_name);
                }
            });
        } catch (UniqueConstraintViolationException) {
            return $this->refuse('This transaction was already processed');
        }

        return response()->json(['ok' => true, 'message' => 'Credit added']);
    }

    private function refuse(string $message): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message]);
    }

    private function fullName(string $name): string
    {
        return count(explode(' ', $name)) === 1 ? $name.' Resident' : $name;
    }
}
