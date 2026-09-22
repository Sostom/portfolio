<?php
// Utilitaires pour le portfolio

// Générer un slug unique à partir d'un titre
function slugify(string $str): string {
    $str = mb_strtolower(trim($str), 'UTF-8');
    $str = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    $str = trim($str, '-');
    return $str ?: 'projet';
}

// Générer un token CSRF unique par session
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérifier le token CSRF
function csrf_verify(string $token): bool {
    if (empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Flash messages (stockés en session)
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (empty($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

// Récupérer la catégorie avec label français
function category_label(string $cat): string {
    $labels = [
        'montage'   => 'Montage vidéo',
        'cadrage'   => 'Cadrage',
        'motion'    => 'Motion design',
        'reportage' => 'Reportage',
        'autre'     => 'Autre',
    ];
    return $labels[$cat] ?? $cat;
}

// Formater une date
function format_date(string $date): string {
    $d = new DateTime($date);
    return $d->format('d/m/Y');
}
