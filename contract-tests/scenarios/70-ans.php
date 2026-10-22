<?php

// Linden Marketplace ANS callbacks: GET /ans.php?<query> with X-ANS-Verify-Hash = sha1(query . ansSalt).
$purchase = function (array $overrides = []): array {
    return array_merge([
        "TransactionID" => "4f5e6d7c",
        "Type" => "Purchase",
        "PayerName" => "Contract Client",
        "PayerKey" => "c11e0000-0000-4000-8000-000000000001",
        "ReceiverName" => "Contract Client",
        "ReceiverKey" => "c11e0000-0000-4000-8000-000000000001",
        "ItemID" => "24680",
        "PaymentGross" => "100",
    ], $overrides);
};

return [
    "description" => "Marketplace coupon callbacks",
    "seeds" => ["catalog", "coupons"],
    "steps" => [
        ["id" => "missing_hash_header", "kind" => "ans", "query" => $purchase(), "tamper" => ["omit_hash_header" => true]],
        ["id" => "bad_hash", "kind" => "ans", "query" => $purchase(), "tamper" => ["hash" => "invalid"]],
        ["id" => "missing_payer_key", "kind" => "ans", "query" => array_diff_key($purchase(), ["PayerKey" => 1])],
        ["id" => "redelivery", "kind" => "ans", "query" => $purchase(["Type" => "Redelivery"])],
        ["id" => "unknown_listing", "kind" => "ans", "query" => $purchase(["ItemID" => "13579"])],
        ["id" => "wrong_amount", "kind" => "ans", "query" => $purchase(["PaymentGross" => "99"])],
        ["id" => "purchase_self", "kind" => "ans", "query" => $purchase()],
        // the hash has no timestamp; replays are only stopped by the unique SLtransactionUUID column
        ["id" => "replayed_purchase", "kind" => "ans", "query" => $purchase()],
        ["id" => "purchase_gift_single_names", "kind" => "ans",
            "query" => $purchase(["TransactionID" => "4f5e6d7d", "PayerName" => "Giver", "PayerKey" => "9f000000-0000-4000-8000-00000000009f"])],
        ["id" => "credit_message_1", "path" => "Mailserver/Next"],
        ["id" => "credit_message_2", "path" => "Mailserver/Next"],
        ["id" => "credit_message_3", "path" => "Mailserver/Next"],
        ["id" => "credit_message_4", "path" => "Mailserver/Next"],
        ["id" => "credit_message_5", "path" => "Mailserver/Next"],
    ],
];
