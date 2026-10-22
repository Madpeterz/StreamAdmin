-- Marketplace coupons (ANS) enabled with one tracked listing.
UPDATE `slconfig` SET `enableCoupons` = 1 WHERE `id` = 1;

INSERT INTO `marketplacecoupons` (`id`, `cost`, `listingid`, `credit`, `claims`, `lastClaim`) VALUES
(1, 100, 24680, 120, 0, 0);
