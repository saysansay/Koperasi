<?php

return [
    ['key' => 'dashboard', 'label' => 'dashboard', 'route' => 'dashboard', 'icon' => 'bi-grid', 'roles' => ['admin', 'manager', 'staff', 'member']],
    ['key' => 'my_transactions', 'label' => 'my_transactions', 'route' => 'member-transactions.index', 'icon' => 'bi-clock-history', 'roles' => ['member']],
    ['key' => 'members', 'label' => 'members', 'route' => 'members.index', 'icon' => 'bi-people', 'roles' => ['admin', 'manager', 'staff']],
    ['key' => 'savings', 'label' => 'savings', 'route' => 'savings.index', 'icon' => 'bi-wallet2', 'roles' => ['admin', 'manager', 'staff']],
    ['key' => 'sales_payments', 'label' => 'sales_payments', 'route' => 'sale-payments.index', 'icon' => 'bi-cart-check', 'roles' => ['admin', 'manager', 'staff']],
    ['key' => 'loans', 'label' => 'loans', 'route' => 'loans.index', 'icon' => 'bi-cash-coin', 'roles' => ['admin', 'manager', 'staff']],
    ['key' => 'installments', 'label' => 'installments', 'route' => 'installments.index', 'icon' => 'bi-calendar-check', 'roles' => ['admin', 'manager', 'staff']],
    ['key' => 'accounts', 'label' => 'accounts', 'route' => 'accounts.index', 'icon' => 'bi-journal-text', 'roles' => ['admin', 'manager', 'staff']],
    ['key' => 'journals', 'label' => 'journals', 'route' => 'journals.index', 'icon' => 'bi-receipt', 'roles' => ['admin', 'manager', 'staff']],
    ['key' => 'reports', 'label' => 'reports', 'route' => 'reports.members', 'icon' => 'bi-bar-chart', 'roles' => ['admin', 'manager']],
    ['key' => 'users', 'label' => 'users', 'route' => 'users.index', 'icon' => 'bi-person-lock', 'roles' => ['admin']],
    ['key' => 'settings', 'label' => 'settings', 'route' => 'settings.index', 'icon' => 'bi-gear', 'roles' => ['admin', 'manager']],
];
