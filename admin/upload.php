<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$db = getDB();
$flash = get_flash();
$edit_id = (int) ($_GET['edit'] ?? 0);
$project = null;

if ($edit_id > 0) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $edit_id]);
    $project = $stmt->fetch();
    if (!$project) {
        set_flash('error', 'Projet introuvable.');
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Requête invalide (token CSRF).');
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category    = $_POST['category'] ?? 'montage';
        $featured    = !empty($_POST['featured']) ? 1 : 0;
        $order_num   = (int) ($_POST['order_num'] ?? 0);
        $media_type  = $_POST['media_type'] ?? 'upload';
        $media_url   = trim($_POST['media_url'] ?? '');
        $thumbnail   = trim($_POST['thumbnail_url'] ?? '');

        // Validation
        if ($title === '') {
            set_flash('error', 'Le titre est obligatoire.');
        } elseif ($media_type === 'upload' && $media_url === '') {
            // upload file handled below
        } elseif (($media_type === 'youtube' || $media_type === 'vimeo') && $media_url === '') {
            set_flash('error', 'Veuillez fournir un lien vidéo.');
        } else {
            // Handle file upload for upload mode
            $media_file = null;
            if ($media_type === 'upload' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $allowed = [
                    'video/mp4' => 'mp4', 'video/webm' => 'webm',
                    'application/octet-stream' => 'mp4',
                ];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['video_file']['tmp_name']);
                $ext = $allowed[$mime] ?? null;
                if (!$ext) {
                    set_flash('error', 'Type de fichier non autorisé (MP4, WebM uniquement).');
                } elseif ($_FILES['video_file']['size'] > 50 * 1024 * 1024) {
                    set_flash('error', 'Le fichier dépasse 50 Mo.');
                } else {
                    $new_name = uniqid('vid_') . '.' . $ext;
                    $dest = __DIR__ . '/../assets/uploads/videos/' . $new_name;
                    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest)) {
                        $media_file = 'assets/uploads/videos/' . $new_name;
                    } else {
                        set_flash('error', 'Erreur lors de l\'upload du fichier.');
                    }
                }
            }

            // Handle thumbnail upload if provided
            if (empty($thumbnail) && isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
                $allowed_img = [
                    'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
                ];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['thumbnail_file']['tmp_name']);
                $ext = $allowed_img[$mime] ?? null;
                if ($ext) {
                    $new_name = uniqid('thumb_') . '.' . $ext;
                    $dest = __DIR__ . '/../assets/uploads/images/' . $new_name;
                    if (move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $dest)) {
                        $thumbnail = 'assets/uploads/images/' . $new_name;
                    }
                }
            }

            if (empty($thumbnail) && !empty($media_file)) {
                // Generate thumbnail from video first frame using ffmpeg if available
                $thumbnail = null; // Laissé vide - peut être généré plus tard
            }

            $slug = slugify($title);
            if ($edit_id > 0) {
                // Update
                $stmt = $db->prepare('
                    UPDATE projects SET slug=:slug, title=:t, description=:d, category=:c,
                    thumbnail=:th, media_type=:mt, media_url=:mu, media_file=:mf,
                    featured=:f, order_num=:o, updated_at=NOW()
                    WHERE id=:id
                ');
                $stmt->execute([
                    ':slug' => $slug, ':t' => $title, ':d' => $description, ':c' => $category,
                    ':th' => $thumbnail ?: null, ':mt' => $media_type, ':mu' => $media_url ?: null,
                    ':mf' => $media_file, ':f' => $featured, ':o' => $order_num, ':id' => $edit_id,
                ]);
                // If new thumbnail uploaded, remove old one
                if ($thumbnail && !empty($project['thumbnail']) && file_exists(__DIR__ . '/../' . $project['thumbnail'])) {
                    @unlink(__DIR__ . '/../' . $project['thumbnail']);
                }
                set_flash('success', 'Projet mis à jour.');
            } else {
                // Insert
                $stmt = $db->prepare('
                    INSERT INTO projects (slug, title, description, category, thumbnail, media_type, media_url, media_file, featured, order_num)
                    VALUES (:slug, :t, :d, :c, :th, :mt, :mu, :mf, :f, :o)
                ');
                $stmt->execute([
                    ':slug' => $slug, ':t' => $title, ':d' => $description, ':c' => $category,
                    ':th' => $thumbnail ?: null, ':mt' => $media_type, ':mu' => $media_url ?: null,
                    ':mf' => $media_file, ':f' => $featured, ':o' => $order_num,
                ]);
                set_flash('success', 'Projet créé.');
            }
            header('Location: index.php');
            exit;
        }
    }
}

// Pré-remplir pour édition
$title       = $project ? htmlspecialchars($project['title']) : '';
$description = $project ? htmlspecialchars($project['description']) : '';
$category    = $project ? $project['category'] : 'montage';
$featured    = $project ? (bool) $project['featured'] : false;
$order_num   = $project ? (int) $project['order_num'] : 0;
$media_type  = $project ? $project['media_type'] : 'upload';
$media_url   = $project ? htmlspecialchars($project['media_url']) : '';
$thumbnail   = $project ? htmlspecialchars($project['thumbnail']) : '';
$edit_mode   = (bool) $project;
$token       = csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $edit_mode ? 'Modifier' : 'Nouveau' ?> projet - Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="admin-header">
  <div class="admin-header-inner">
    <a href="index.php" class="admin-brand"><i class="fa-solid fa-circle-dot"></i> Portfolio</a>
    <a href="index.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour</a>
  </div>
</header>

<main class="admin-main">
  <h1><?= $edit_mode ? 'Modifier le projet' : 'Nouveau projet' ?></h1>

  <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" action="upload.php<?= $edit_mode ? '?edit=' . (int) $edit_id : '' ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">

    <div class="form-grid">
      <div class="form-group">
        <label for="title">Titre *</label>
        <input type="text" id="title" name="title" required value="<?= $title ?>">
      </div>
      <div class="form-group">
        <label for="category">Catégorie</label>
        <select id="category" name="category">
          <option value="montage" <?= $category === 'montage' ? 'selected' : '' ?>>Montage vidéo</option>
          <option value="cadrage" <?= $category === 'cadrage' ? 'selected' : '' ?>>Cadrage</option>
          <option value="motion" <?= $category === 'motion' ? 'selected' : '' ?>>Motion design</option>
          <option value="reportage" <?= $category === 'reportage' ? 'selected' : '' ?>>Reportage</option>
          <option value="autre" <?= $category === 'autre' ? 'selected' : '' ?>>Autre</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4"><?= $description ?></textarea>
    </div>

    <div class="form-group">
      <label>Média vidéo</label>
      <div class="radio-group">
        <label class="radio-label">
          <input type="radio" name="media_type" value="upload" <?= $media_type === 'upload' ? 'checked' : '' ?> onchange="toggleMedia(this.value)">
          <span><i class="fa-solid fa-upload"></i> Uploader un fichier</span>
        </label>
        <label class="radio-label">
          <input type="radio" name="media_type" value="youtube" <?= $media_type === 'youtube' ? 'checked' : '' ?> onchange="toggleMedia(this.value)">
          <span><i class="fa-brands fa-youtube"></i> Lien YouTube</span>
        </label>
        <label class="radio-label">
          <input type="radio" name="media_type" value="vimeo" <?= $media_type === 'vimeo' ? 'checked' : '' ?> onchange="toggleMedia(this.value)">
          <span><i class="fa-brands fa-vimeo"></i> Lien Vimeo</span>
        </label>
      </div>
    </div>

    <div id="media-upload" class="media-section" style="display:<?= $media_type === 'upload' ? 'block' : 'none' ?>">
      <div class="form-group">
        <label for="video_file">Fichier vidéo (MP4 / WebM, max 50 Mo)</label>
        <input type="file" id="video_file" name="video_file" accept=".mp4,.webm,video/mp4,video/webm">
      </div>
    </div>

    <div id="media-link" class="media-section" style="display:<?= $media_type !== 'upload' ? 'block' : 'none' ?>">
      <div class="form-group">
        <label for="media_url">URL vidéo</label>
        <input type="url" id="media_url" name="media_url" placeholder="https://www.youtube.com/watch?v=..." value="<?= $media_url ?>">
        <small class="form-help" id="media-help"></small>
      </div>
    </div>

    <div class="form-group">
      <label>Miniature (thumbnail)</label>
      <div class="thumbnail-row">
        <div class="form-group" style="flex: 1">
          <input type="text" name="thumbnail_url" placeholder="URL de la miniature" value="<?= $thumbnail ?>">
        </div>
        <div class="form-group" style="flex: 1">
          <input type="file" name="thumbnail_file" accept="image/*">
        </div>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label for="order_num">Ordre d'affichage</label>
        <input type="number" id="order_num" name="order_num" min="0" value="<?= $order_num ?>">
      </div>
      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" name="featured" value="1" <?= $featured ? 'checked' : '' ?>>
          <span>Mettre en vedette</span>
        </label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg"><?= $edit_mode ? 'Mettre à jour' : 'Créer le projet' ?></button>
  </form>
</main>

<script src="../assets/js/main.js"></script>
<script>
function toggleMedia(type) {
  document.getElementById('media-upload').style.display = type === 'upload' ? 'block' : 'none';
  document.getElementById('media-link').style.display = type === 'upload' ? 'none' : 'block';
}
// Extrait l'ID YouTube/Vimeo pour validation en temps réel
document.getElementById('media_url')?.addEventListener('input', function(e) {
  const url = e.target.value;
  const help = document.getElementById('media-help');
  if (!help) return;
  const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/);
  const vimMatch = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
  if (ytMatch) help.textContent = 'YouTube ID: ' + ytMatch[1];
  else if (vimMatch) help.textContent = 'Vimeo ID: ' + vimMatch[1];
  else help.textContent = '';
});
</script>
</body>
</html>
