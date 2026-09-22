<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$db = getDB();
$flash = get_flash();

// Récupérer le profil
$stmt = $db->query('SELECT id, username, avatar FROM users LIMIT 1');
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Requête invalide (token CSRF).');
    } else {
        // Gestion de l'avatar
        $avatar = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = [
                'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
            ];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['avatar']['tmp_name']);
            $ext = $allowed[$mime] ?? null;
            if ($ext) {
                $new_name = 'avatar_' . uniqid() . '.' . $ext;
                $dest = __DIR__ . '/../assets/uploads/images/' . $new_name;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                    $avatar = 'assets/uploads/images/' . $new_name;
                    // Supprimer ancien avatar
                    if (!empty($user['avatar']) && file_exists(__DIR__ . '/../' . $user['avatar'])) {
                        @unlink(__DIR__ . '/../' . $user['avatar']);
                    }
                } else {
                    set_flash('error', 'Erreur lors de l\'upload de la photo.');
                }
            } else {
                set_flash('error', 'Type de fichier non autorisé (JPG, PNG, WebP uniquement).');
            }
        }

        if (empty($flash)) {
            $stmt = $db->prepare('UPDATE users SET avatar = :a WHERE id = :id');
            $stmt->execute([':a' => $avatar ?: null, ':id' => (int) $user['id']]);
            set_flash('success', 'Profil mis à jour.');
        }
    }
}

// Recharger l'utilisateur après mise à jour
$stmt = $db->query('SELECT id, username, avatar FROM users LIMIT 1');
$user = $stmt->fetch();
$avatar = $user['avatar'];
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil - Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="admin-header">
  <div class="admin-header-inner">
    <a href="/portfolio/" class="admin-brand"><i class="fa-solid fa-circle-dot"></i> Portfolio</a>
    <div class="admin-actions">
      <a href="/portfolio/" class="btn btn-outline btn-sm" target="_blank"><i class="fa-solid fa-eye"></i> Voir le site</a>
      <a href="logout.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </div>
  </div>
</header>

<main class="admin-main">
  <h1>Mon Profil</h1>

  <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endif; ?>

  <div class="profil-card">
    <div class="profil-avatar-wrap">
      <?php if (!empty($avatar)): ?>
        <img src="<?= htmlspecialchars($avatar) ?>" alt="Photo de profil" class="profil-avatar" id="admin-avatar">
      <?php else: ?>
        <div class="profil-avatar profil-avatar-empty"><i class="fa-solid fa-user"></i></div>
      <?php endif; ?>
      <a href="#" class="profil-avatar-zoom" id="zoom-avatar" title="Agrandir la photo"><i class="fa-solid fa-expand"></i></a>
    </div>

    <div class="profil-info">
      <h2><?= htmlspecialchars($user['username']) ?></h2>
      <p class="profil-role">Portfolio Vidéo</p>
      <p class="profil-bio">
        Jeune professionnel passionné par l'audiovisuel, la communication et la création de contenus multimédias.
        Spécialisé en cadrage vidéo et montage vidéo. Créatif, curieux et motivé par l'apprentissage.
      </p>
    </div>
  </div>

  <h3 style="margin: 32px 0 16px; font-size: 1.2rem;">Changer ma photo de profil</h3>
  <form method="post" enctype="multipart/form-data" action="profil.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
    <div class="form-group">
      <label for="avatar">Photo professionnelle (JPG, PNG ou WebP)</label>
      <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp">
      <small class="form-help">Taille recommandée : 400x400px minimum. JPG, PNG ou WebP.</small>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-camera"></i> Mettre à jour la photo</button>
  </form>
</main>

<!-- Lightbox pour la photo de profil -->
<div class="lightbox" id="lightbox-avatar" style="display:none">
  <div class="lightbox-backdrop"></div>
  <div class="lightbox-content">
    <button class="lightbox-close" onclick="document.getElementById('lightbox-avatar').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
    <?php if (!empty($avatar)): ?>
      <img src="<?= htmlspecialchars($avatar) ?>" alt="Photo de profil agrandie" id="lightbox-img">
    <?php endif; ?>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
// Zoom photo profil
document.getElementById('zoom-avatar')?.addEventListener('click', function(e) {
  e.preventDefault();
  document.getElementById('lightbox-avatar').style.display = 'flex';
});
</script>

<!-- Theme Toggle -->
<button class="theme-toggle" onclick="toggleTheme()" title="Changer de thème">
  <i class="fa-solid fa-sun" id="theme-toggle-icon"></i>
</button>
</body>
</html>
