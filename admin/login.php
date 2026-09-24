<?php
define('ROOT', __DIR__ . '/..');
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/auth.php';
require_once ROOT . '/includes/functions.php';

// Déjà connecté → dashboard
if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Requête invalide (token CSRF).';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            $db = getDB();
            $stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                admin_login((int) $user['id']);
                header('Location: index.php');
                exit;
            } else {
                $error = 'Identifiants incorrects.';
            }
        }
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion Admin - Portfolio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-page">
  <!-- Left Panel -->
  <div class="login-left">
    <div class="login-left-content">
      <div class="login-brand">
        <i class="fa-solid fa-circle-dot"></i>
        <span>TZM</span>
      </div>
      <h1 class="login-left-title">Portfolio<br><span class="gradient-text">Admin</span></h1>
      <p class="login-left-subtitle">Gérez vos projets vidéo, mettez à jour votre portfolio et accédez à votre espace professionnel.</p>
      <div class="login-left-features">
        <div class="feature"><i class="fa-solid fa-video"></i> Gestion projets</div>
        <div class="feature"><i class="fa-solid fa-upload"></i> Upload médias</div>
        <div class="feature"><i class="fa-solid fa-palette"></i> Personnalisation</div>
      </div>
    </div>
  </div>

  <!-- Right Panel -->
  <div class="login-right">
    <div class="login-card">
      <div class="login-header">
        <h2>Bienvenue</h2>
        <p>Connectez-vous pour accéder à votre espace admin</p>
      </div>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
        <div class="form-group">
          <label for="username">Nom d'utilisateur</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-user"></i>
            <input type="text" id="username" name="username" required autocomplete="username" placeholder="admin">
          </div>
        </div>
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i class="fa-solid fa-right-to-bracket"></i> Se connecter
        </button>
      </form>
      <div class="login-footer">
        <a href="<?= BASE ?>/"><i class="fa-solid fa-arrow-left"></i> Retour au site public</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>