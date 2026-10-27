<?php
/** Dashboard: counters, requests by service, latest requests. */
$stats = [
    'new' => (int)val("SELECT COUNT(*) FROM requests WHERE status='new'"),
    'month' => (int)val('SELECT COUNT(*) FROM requests WHERE created_at>=?', [date('Y-m-01 00:00:00')]),
    'total' => (int)val('SELECT COUNT(*) FROM requests'),
    'services' => (int)val('SELECT COUNT(*) FROM services WHERE active=1'),
    'partners' => (int)val('SELECT COUNT(*) FROM partners WHERE active=1'),
];
$byService = rows('SELECT service_title, COUNT(*) AS n FROM requests GROUP BY service_title ORDER BY n DESC');
$latest = rows('SELECT * FROM requests ORDER BY id DESC LIMIT 8');

admin_render('dashboard', ['title' => __('nav_dashboard'), 'stats' => $stats, 'latest' => $latest, 'byService' => $byService]);
