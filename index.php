<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Projects for the home page (featured first, then by order)
$stmt = $db->query('SELECT * FROM projects ORDER BY featured DESC, order_num ASC, created_at DESC');
$projects = $stmt->fetchAll();

// User profile
$stmt = $db->query('SELECT id, username, avatar FROM users LIMIT 1');
$user = $stmt->fetch();
$avatar = $user['avatar'];
$username = $user['username'];

// Single project view
$project = null;
$slug = $_GET['slug'] ?? '';
if ($slug) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE slug = :s LIMIT 1');
    $stmt->execute([':s' => $slug]);
    $project = $stmt->fetch();
}

// Prev / next navigation
$prev = null;
$next = null;
if ($project) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE order_num < :o ORDER BY order_num DESC LIMIT 1');
    $stmt->execute([':o' => $project['order_num']]);
    $prev = $stmt->fetch();
    $stmt = $db->prepare('SELECT * FROM projects WHERE order_num > :o ORDER BY order_num ASC LIMIT 1');
    $stmt->execute([':o' => $project['order_num']]);
    $next = $stmt->fetch();
}

$cats = ['montage', 'cadrage', 'motion', 'reportage', 'autre'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $project ? htmlspecialchars($project['title']) . ' — ' : '' ?>Tokou Zime Maximilien | Portfolio Vidéo</title>
<meta name="description" content="Portfolio de Tokou Zime Maximilien - Montage et cadrage vidéo à Cotonou, Bénin.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Scroll Progress -->
<div id="scroll-progress" style="position:fixed;top:0;left:0;height:2px;background:var(--gradient-primary);z-index:1000;width:0;transition:width 0.1s;"></div>

<!-- Top Controls -->
<div class="top-controls">
  <button class="theme-toggle" onclick="toggleTheme()" title="Changer de thème">
    <i class="fa-solid fa-sun" id="theme-toggle-icon"></i>
  </button>
  <div class="lang-switch">
    <span class="lang-switch-item" data-lang="fr" onclick="setLanguage('fr')">FR</span>
    <span class="lang-switch-item" data-lang="en" onclick="setLanguage('en')">EN</span>
  </div>
</div>

<!-- Navbar -->
<nav class="navbar">
  <div class="container navbar-inner">
    <a href="/portfolio/" class="navbar-logo"><i class="fa-solid fa-circle-dot"></i> TZM</a>
    <div class="nav-links">
      <a href="#projets" class="nav-link" data-i18n="nav_projets">Projets</a>
      <a href="#competences" class="nav-link" data-i18n="nav_competences">Compétences</a>
      <a href="#contact" class="nav-link" data-i18n="nav_contact">Contact</a>
      <a href="/portfolio/admin/login.php" class="nav-link nav-admin"><i class="fa-solid fa-lock"></i> Admin</a>
    </div>
    <button class="hamburger" id="hamburger"><i class="fa-solid fa-bars"></i></button>
  </div>
</nav>
<div class="mobile-menu" id="mobile-menu">
  <a href="#projets" data-i18n="nav_projets">Projets</a>
  <a href="#competences" data-i18n="nav_competences">Compétences</a>
  <a href="#contact" data-i18n="nav_contact">Contact</a>
  <a href="/portfolio/admin/login.php"><i class="fa-solid fa-lock"></i> Admin</a>
</div>

<?php if ($project): ?>
  <!-- PROJECT DETAIL PAGE -->
  <section style="padding: 80px 0;">
    <div class="container">
      <a href="/portfolio/" class="btn btn-outline btn-sm" style="margin-bottom: 24px;">
        <i class="fa-solid fa-arrow-left"></i> Retour aux projets
      </a>
      <h1 style="margin-bottom: 12px;"><?= htmlspecialchars($project['title']) ?></h1>
      <div class="project-meta" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom: 28px;">
        <span class="badge"><?= category_label($project['category']) ?></span>
        <?php if ($project['featured']): ?><span class="badge badge-rose">⭐ En vedette</span><?php endif; ?>
        <span class="badge badge-muted"><?= format_date($project['created_at']) ?></span>
      </div>

      <div style="position:relative; width:100%; aspect-ratio: 16/9; background: var(--bg-elevated); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 24px;">
        <?php if ($project['media_type'] === 'youtube'): ?>
          <?php preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $project['media_url'] ?? '', $m); ?>
          <iframe src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($m[1] ?? '') ?>?rel=0&modestbranding=1"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen loading="lazy" style="position:absolute; inset:0; width:100%; height:100%; border:none;"></iframe>
        <?php elseif ($project['media_type'] === 'vimeo'): ?>
          <?php preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $project['media_url'] ?? '', $m); ?>
          <iframe src="https://player.vimeo.com/video/<?= htmlspecialchars($m[1] ?? '') ?>?title=0&byline=0&portrait=0"
                  allow="autoplay; fullscreen; picture-in-picture"
                  allowfullscreen loading="lazy" style="position:absolute; inset:0; width:100%; height:100%; border:none;"></iframe>
        <?php elseif ($project['media_type'] === 'upload' && !empty($project['media_file'])): ?>
          <video controls style="width:100%; height:100%; object-fit:cover;">
            <source src="<?= htmlspecialchars($project['media_file']) ?>" type="video/mp4">
            Votre navigateur ne supporte pas la lecture vidéo.
          </video>
        <?php else: ?>
          <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color: var(--text-muted);">
            <p><i class="fa-solid fa-film" style="font-size: 2rem; margin-right: 12px;"></i> Aucun média disponible.</p>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($project['description'])): ?>
        <div style="color: var(--text-secondary); line-height: 1.8; max-width: 800px;">
          <?= nl2br(htmlspecialchars($project['description'])) ?>
        </div>
      <?php endif; ?>

      <div style="display:flex; gap:16px; margin-top: 40px; flex-wrap:wrap;">
        <?php if ($prev): ?>
          <a href="/portfolio/index.php?slug=<?= htmlspecialchars($prev['slug']) ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($prev['title']) ?>
          </a>
        <?php endif; ?>
        <?php if ($next): ?>
          <a href="/portfolio/index.php?slug=<?= htmlspecialchars($next['slug']) ?>" class="btn btn-outline btn-right">
            <?= htmlspecialchars($next['title']) ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>

<?php else: ?>
  <!-- PUBLIC HOME PAGE -->

  <!-- HERO -->
  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <span class="hero-tag"><i class="fa-solid fa-circle" style="font-size:0.5rem;"></i> <span data-i18n="hero_tag">Portfolio Vidéo</span></span>
        <h1 class="hero-title">
          Tokou Zime Maximilien
        </h1>
        <p class="hero-subtitle" data-i18n="hero_subtitle">
          Passionné par l'audiovisuel, la création de contenus multimédias et le storytelling visuel à Cotonou, Bénin.
        </p>
        <div class="hero-actions">
          <a href="#projets" class="btn btn-primary btn-lg"><i class="fa-solid fa-play"></i> <span data-i18n="hero_cta_projets">Voir les projets</span></a>
          <a href="#contact" class="btn btn-outline btn-lg"><span data-i18n="hero_cta_contact">Me contacter</span></a>
        </div>
      </div>
    </div>
    <a href="#profil" class="scroll-indicator"><i class="fa-solid fa-chevron-down"></i></a>
  </section>

  <!-- PROFIL -->
  <section class="profil-section" id="profil">
    <div class="container">
      <h2 class="section-title"><span data-i18n="section_profil">Mon Profil</span></h2>
      <p class="section-subtitle" data-i18n="section_profil_subtitle">Qui suis-je et ce que je fais.</p>
      <div class="profil-grid">
        <div class="profil-photo-wrap">
          <?php if (!empty($avatar)): ?>
            <img src="<?= htmlspecialchars($avatar) ?>" alt="Photo de profil" class="profil-photo" data-zoom="#profil-photo">
          <?php else: ?>
            <div class="profil-photo-empty"><i class="fa-solid fa-user"></i></div>
          <?php endif; ?>
          <?php if (!empty($avatar)): ?>
            <a href="#" class="profil-photo-zoom" data-zoom="#profil-photo"><i class="fa-solid fa-expand"></i> <span data-i18n="zoom">Agrandir</span></a>
          <?php endif; ?>
        </div>
        <div class="profil-info">
          <h2><?= htmlspecialchars($username) ?></h2>
          <p class="profil-role" data-i18n="profil_role">Vidéaste · Monteur · Cadreur</p>
          <p class="profil-bio">
            Jeune professionnel passionné par l'audiovisuel, la communication et la création de contenus multimédias.
            Mon parcours m'a permis de développer des compétences solides en cadrage vidéo, montage vidéo et storytelling.
            Créatif, curieux et rigoureux, je m'investis dans chaque projet avec exigence et créativité.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- COMPÉTENCES -->
  <section id="competences" class="competences-section">
    <div class="container">
      <h2 class="section-title"><span data-i18n="section_competences">Mes Compétences</span></h2>
      <p class="section-subtitle" data-i18n="section_competences_subtitle">Les outils et savoir-faire que je mets à votre service.</p>
      <div class="competences-grid">
        <div class="competence-card">
          <div class="competence-header">
            <div class="competence-icon"><i class="fa-solid fa-video"></i></div>
            <h3>Montage Vidéo</h3>
          </div>
          <div class="competence-tags">
            <span class="competence-tag">Adobe Premiere Pro</span>
            <span class="competence-tag">CapCut</span>
            <span class="competence-tag">Cutting</span>
            <span class="competence-tag">Étalonnage</span>
          </div>
          <div class="competence-level">
            <span>Intermédiaire+</span>
            <div class="level-bar"><div class="level-bar-fill" style="width:75%"></div></div>
          </div>
        </div>
        <div class="competence-card">
          <div class="competence-header">
            <div class="competence-icon"><i class="fa-solid fa-camera"></i></div>
            <h3>Cadrage & Captation</h3>
          </div>
          <div class="competence-tags">
            <span class="competence-tag">Cadrage subjectif</span>
            <span class="competence-tag">Cadrage plan large</span>
            <span class="competence-tag">Stabilisation</span>
            <span class="competence-tag">Éclairage</span>
          </div>
          <div class="competence-level">
            <span>Intermédiaire+</span>
            <div class="level-bar"><div class="level-bar-fill" style="width:70%"></div></div>
          </div>
        </div>
        <div class="competence-card">
          <div class="competence-header">
            <div class="competence-icon"><i class="fa-solid fa-palette"></i></div>
            <h3>Création Contenu</h3>
          </div>
          <div class="competence-tags">
            <span class="competence-tag">Canva</span>
            <span class="competence-tag">Motion design</span>
            <span class="competence-tag">Thumbnails</span>
            <span class="competence-tag">Narratif</span>
          </div>
          <div class="competence-level">
            <span>Intermédiaire</span>
            <div class="level-bar"><div class="level-bar-fill" style="width:65%"></div></div>
          </div>
        </div>
        <div class="competence-card">
          <div class="competence-header">
            <div class="competence-icon"><i class="fa-solid fa-headphones"></i></div>
            <h3>Son & Audio</h3>
          </div>
          <div class="competence-tags">
            <span class="competence-tag">BandLab</span>
            <span class="competence-tag">Mixage</span>
            <span class="competence-tag">Post-production</span>
          </div>
          <div class="competence-level">
            <span>Notions pratiques</span>
            <div class="level-bar"><div class="level-bar-fill" style="width:50%"></div></div>
          </div>
        </div>
        <div class="competence-card">
          <div class="competence-header">
            <div class="competence-icon"><i class="fa-solid fa-laptop-code"></i></div>
            <h3>Digital & Réseaux</h3>
          </div>
          <div class="competence-tags">
            <span class="competence-tag">TikTok</span>
            <span class="competence-tag">Instagram</span>
            <span class="competence-tag">YouTube</span>
            <span class="competence-tag">SEO</span>
          </div>
          <div class="competence-level">
            <span>Expérimenté</span>
            <div class="level-bar"><div class="level-bar-fill" style="width:80%"></div></div>
          </div>
        </div>
        <div class="competence-card">
          <div class="competence-header">
            <div class="competence-icon"><i class="fa-solid fa-language"></i></div>
            <h3>Langues</h3>
          </div>
          <div class="competence-tags">
            <span class="competence-tag">Français</span>
            <span class="competence-tag">Anglais</span>
            <span class="competence-tag">Bariba</span>
            <span class="competence-tag">Fon</span>
          </div>
          <div class="competence-level">
            <span>Courant à intermédiaire</span>
            <div class="level-bar"><div class="level-bar-fill" style="width:65%"></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PROJETS -->
  <section id="projets" class="projects-section">
    <div class="container">
      <h2 class="section-title"><span data-i18n="section_projets">Mes Projets</span></h2>
      <p class="section-subtitle" data-i18n="section_projets_subtitle">Une sélection de réalisations.</p>
      <div class="filters">
        <button class="filter-btn active" data-filter="all" data-i18n="filter_all">Tout</button>
        <?php foreach ($cats as $c): ?>
          <button class="filter-btn" data-filter="<?= $c ?>"><?= category_label($c) ?></button>
        <?php endforeach; ?>
      </div>
      <?php if (count($projects) === 0): ?>
        <div class="empty-state">
          <i class="fa-solid fa-film"></i>
          <p>Aucun projet publié pour le moment.</p>
        </div>
      <?php else: ?>
        <div class="projects-grid">
          <?php foreach ($projects as $p): ?>
            <article class="project-card" data-category="<?= htmlspecialchars($p['category']) ?>">
              <a href="/portfolio/index.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="project-card-link">
                <?php if (!empty($p['thumbnail'])): ?>
                  <img src="<?= htmlspecialchars($p['thumbnail']) ?>" alt="" class="project-thumb" loading="lazy">
                <?php else: ?>
                  <div class="project-thumb project-thumb-empty"><i class="fa-solid fa-film"></i></div>
                <?php endif; ?>
                <div class="project-overlay">
                  <?php if ($p['media_type'] === 'youtube'): ?><i class="fa-brands fa-youtube"></i>
                  <?php elseif ($p['media_type'] === 'vimeo'): ?><i class="fa-brands fa-vimeo"></i>
                  <?php else: ?><i class="fa-solid fa-play"></i><?php endif; ?>
                </div>
                <div class="project-body">
                  <span class="badge"><?= category_label($p['category']) ?></span>
                  <h3><?= htmlspecialchars($p['title']) ?></h3>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact" class="contact-section">
    <div class="container">
      <h2 class="section-title"><span data-i18n="section_contact">Me Contacter</span></h2>
      <div class="contact-grid">
        <div class="contact-card">
          <i class="fa-solid fa-envelope contact-icon"></i>
          <h3 data-i18n="contact_email">Email</h3>
          <a href="mailto:tokoumaximilien@gmail.com">tokoumaximilien@gmail.com</a>
        </div>
        <div class="contact-card">
          <i class="fa-solid fa-phone contact-icon"></i>
          <h3 data-i18n="contact_phone">Téléphone</h3>
          <a href="tel:+2290194957775">+229 01 94 95 77 75</a>
          <a href="tel:+2290147156053">+229 01 47 15 60 53</a>
        </div>
        <div class="contact-card">
          <i class="fa-solid fa-location-dot contact-icon"></i>
          <h3 data-i18n="contact_location">Localisation</h3>
          <p>Cotonou, Bénin</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container">
      <p data-i18n="footer">&copy; 2025 Tokou Zime Maximilien. <span data-i18n="footer_copyright">Tous droits réservés.</span></p>
    </div>
  </footer>
<?php endif; ?>

<button class="back-to-top"><i class="fa-solid fa-arrow-up"></i></button>
<div class="lightbox" id="lightbox">
  <div class="lightbox-backdrop"></div>
  <div class="lightbox-content">
    <button class="lightbox-close"><i class="fa-solid fa-xmark"></i></button>
    <img src="" alt="Agrandir" id="lightbox-img">
  </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
