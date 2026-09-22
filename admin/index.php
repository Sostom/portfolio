<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$db = getDB();
$flash = get_flash();

// Récupérer les projets ordonnés
$stmt = $db->query('SELECT * FROM projects ORDER BY order_num ASC, id DESC');
$projects = $stmt->fetchAll();

// Récupérer le profil utilisateur (avatar, etc.)
$stmt = $db->query('SELECT id, username, avatar FROM users LIMIT 1');
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - Portfolio</title>
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
      <a href="profil.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-user"></i> Profil</a>
      <a href="logout.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </div>
  </div>
</header>

<main class="admin-main">
  <div class="admin-top">
    <h1>Mes Projets</h1>
    <a href="upload.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouveau projet</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endif; ?>

  <?php if (count($projects) === 0): ?>
    <div class="empty-state">
      <i class="fa-solid fa-film"></i>
      <p>Aucun projet pour le moment.</p>
      <a href="upload.php" class="btn btn-primary">Ajouter un projet</a>
    </div>
  <?php else: ?>
    <div class="projects-list">
      <?php foreach ($projects as $p): ?>
        <div class="project-row" data-id="<?= (int) $p['id'] ?>">
          <div class="drag-handle" title="Glisser pour réordonner">
            <i class="fa-solid fa-grip-vertical"></i>
          </div>
          <?php if ($p['thumbnail']): ?>
            <img src="<?= htmlspecialchars($p['thumbnail']) ?>" alt="" class="project-thumb">
          <?php else: ?>
            <div class="project-thumb project-thumb-empty"><i class="fa-solid fa-image"></i></div>
          <?php endif; ?>
          <div class="project-info">
            <h3><?= htmlspecialchars($p['title']) ?></h3>
            <div class="project-meta">
              <span class="badge badge-cat"><?= category_label($p['category']) ?></span>
              <?php if ($p['media_type'] === 'youtube'): ?><span class="badge"><i class="fa-brands fa-youtube"></i> YouTube</span><?php endif; ?>
              <?php if ($p['media_type'] === 'vimeo'): ?><span class="badge"><i class="fa-brands fa-vimeo"></i> Vimeo</span><?php endif; ?>
              <?php if ($p['media_type'] === 'upload'): ?><span class="badge"><i class="fa-solid fa-file-video"></i> Fichier</span><?php endif; ?>
              <?php if ($p['featured']): ?><span class="badge badge-featured">⭐ En vedette</span><?php endif; ?>
            </div>
          </div>
          <div class="project-actions">
            <a href="upload.php?edit=<?= (int) $p['id'] ?>" class="btn btn-icon" title="Modifier"><i class="fa-solid fa-pen"></i></a>
            <button class="btn btn-icon btn-danger" data-delete="<?= (int) $p['id'] ?>" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<form method="post" id="delete-form" action="delete.php" style="display:none">
  <input type="hidden" name="id" id="delete-id">
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
</form>

<script src="../assets/js/main.js"></script>

<!-- Theme Toggle -->
<button class="theme-toggle" onclick="toggleTheme()" title="Changer de thème">
  <i class="fa-solid fa-sun" id="theme-toggle-icon"></i>
</button>
</body>
</html>
