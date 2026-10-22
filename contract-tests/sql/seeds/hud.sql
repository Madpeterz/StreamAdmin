-- HUD features enabled.
UPDATE `slconfig` SET `hudAllowDiscord` = 1, `hudDiscordLink` = 'https://discord.contract.test/invite',
    `hudAllowGroup` = 1, `hudGroupLink` = 'secondlife:///app/group/9c000000-0000-4000-8000-000000000009/about',
    `hudAllowDetails` = 1, `hudAllowRenewal` = 1 WHERE `id` = 1;
