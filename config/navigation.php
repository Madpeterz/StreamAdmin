<?php

/*
 * Sidebar sections (Dashboard is always first). `owner` items are only shown to owner level staff.
 * Icons are Heroicons names as used by Flux.
 */

return [
    'Rentals' => [
        ['label' => 'Clients', 'icon' => 'users', 'route' => 'clients.index'],
        ['label' => 'Streams', 'icon' => 'signal', 'route' => 'streams.index'],
        ['label' => 'Packages', 'icon' => 'cube', 'route' => 'packages.index'],
        ['label' => 'Servers', 'icon' => 'server-stack', 'route' => 'servers.index'],
    ],
    'Sales' => [
        ['label' => 'Transactions', 'icon' => 'banknotes', 'route' => 'transactions.index'],
        ['label' => 'Reports', 'icon' => 'chart-bar', 'route' => 'reports.index'],
        ['label' => 'Resellers', 'icon' => 'user-group', 'route' => 'resellers.index'],
        ['label' => 'Tree vendors', 'icon' => 'squares-2x2', 'route' => 'tree-vendors.index'],
        ['label' => 'Coupons', 'icon' => 'ticket', 'route' => 'coupons.index'],
    ],
    'Messaging' => [
        ['label' => 'Outbox', 'icon' => 'inbox-stack', 'route' => 'outbox.index'],
        ['label' => 'Notices', 'icon' => 'bell-alert', 'route' => 'notices.index'],
        ['label' => 'Templates', 'icon' => 'document-text', 'route' => 'templates.index'],
        ['label' => 'Bot', 'icon' => 'cpu-chip', 'route' => 'bot.index', 'owner' => true],
    ],
    'System' => [
        ['label' => 'Health', 'icon' => 'heart', 'route' => 'health.index'],
        ['label' => 'Avatars', 'icon' => 'user-circle', 'route' => 'avatars.index'],
        ['label' => 'Bans', 'icon' => 'no-symbol', 'route' => 'bans.index', 'owner' => true],
        ['label' => 'Texture packs', 'icon' => 'photo', 'route' => 'texture-packs.index'],
        ['label' => 'Audit log', 'icon' => 'clipboard-document-list', 'route' => 'audit-log.index', 'owner' => true],
        ['label' => 'Staff', 'icon' => 'identification', 'route' => 'staff.index', 'owner' => true],
        ['label' => 'Settings', 'icon' => 'cog-6-tooth', 'route' => 'system-settings.index', 'owner' => true],
    ],
];
