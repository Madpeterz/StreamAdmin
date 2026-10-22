<?php

// ANS callbacks while coupons are disabled.
return [
    "description" => "Marketplace coupon callbacks with coupons disabled",
    "seeds" => ["catalog"],
    "steps" => [
        ["id" => "purchase_disabled", "kind" => "ans", "query" => [
            "TransactionID" => "4f5e6d7c", "Type" => "Purchase",
            "PayerName" => "Contract Client", "PayerKey" => "c11e0000-0000-4000-8000-000000000001",
            "ReceiverName" => "Contract Client", "ReceiverKey" => "c11e0000-0000-4000-8000-000000000001",
            "ItemID" => "24680", "PaymentGross" => "100",
        ]],
        ["id" => "no_query", "kind" => "ans", "query" => []],
    ],
];
