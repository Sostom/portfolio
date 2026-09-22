# Portfolio Vidéo - Tokou Zime Maximilien

Portfolio professionnel moderne avec design néon pour monteur et cadreur vidéo.

## 🎯 Caractéristiques

- ✨ Design néon moderne (noir + cyan/violet électrique)
- 🎬 Grille de projets filtrable par catégorie
- 📹 Support YouTube, Vimeo et upload direct de vidéos
- 🔐 Espace admin sécurisé pour gérer les projets
- 📱 100% responsive (mobile, tablet, desktop)
- ⚡ Animations fluides et typewriter effect

## 🛠️ Stack Technique

- **Backend**: PHP 8.2 + MySQL (MariaDB)
- **Frontend**: HTML5, CSS3 (variables CSS), JavaScript vanilla
- **Fonts**: Google Fonts (Outfit + Inter)
- **Icons**: Font Awesome 6.5

## 📦 Installation

### 1. Prérequis
- XAMPP installé avec Apache et MySQL/MariaDB
- PHP 8.x avec extensions PDO, MySQLi activées

### 2. Base de données

Lancer MySQL depuis XAMPP Control Panel, puis :

```bash
cd C:\xampp\htdocs\portfolio
C:\xampp\mysql\bin\mysql -u root < data/schema.sql
```

Ou manuellement :
```sql
CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;
-- Puis copier-coller le contenu de data/schema.sql
```

### 3. Configuration

Vérifier `includes/db.php` :
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Vide par défaut sur XAMPP
```

### 4. Permissions uploads

Les dossiers `assets/uploads/videos/` et `assets/uploads/images/` doivent être accessibles en écriture par PHP.

## 🔑 Identifiants Admin

- **URL**: http://localhost/portfolio/admin/login.php
- **Username**: `admin`
- **Password**: `admin2025!`

⚠️ **À changer immédiatement en production !**

Pour générer un nouveau hash bcrypt :
```bash
php -r "echo password_hash('votre_nouveau_mdp', PASSWORD_BCRYPT);"
```
Puis mettre à jour dans la table `users`.

## 🚀 Utilisation

### Page publique
- Accès: http://localhost/portfolio/
- Hero avec effet typewriter
- Grille de projets filtrable
- Contact en footer

### Espace Admin
1. Se connecter via http://localhost/portfolio/admin/login.php
2. **Dashboard**: liste des projets avec drag-and-drop (ordre)
3. **Nouveau projet**: 
   - Titre, description, catégorie
   - Choisir : upload fichier MP4/WebM OU lien YouTube/Vimeo
   - Thumbnail (URL ou upload image)
   - Mettre en vedette, définir ordre
4. **Modifier/Supprimer** depuis le dashboard

### Upload de vidéos
- **Option 1**: Coller lien YouTube ou Vimeo (recommandé, léger)
- **Option 2**: Upload direct MP4/WebM (max 50 Mo par défaut)

## 📁 Structure

```
portfolio/
├── index.php              # Page publique (hero + projets + contact)
├── admin/
│   ├── login.php          # Authentification
│   ├── index.php          # Dashboard
│   ├── upload.php         # Créer/modifier projet
│   ├── delete.php         # Supprimer projet
│   └── logout.php         # Déconnexion
├── includes/
│   ├── db.php             # Connexion PDO
│   ├── auth.php           # Sessions + vérification admin
│   └── functions.php      # Utilitaires (slug, CSRF, flash)
├── assets/
│   ├── css/style.css      # Design néon complet
│   ├── js/main.js         # Animations + filtres + typewriter
│   └── uploads/           # Fichiers uploadés
└── data/
    └── schema.sql         # Structure DB + seed
```

## 🎨 Personnalisation

### Couleurs néon
Modifier les variables CSS dans `assets/css/style.css` :
```css
:root {
  --neon-cyan: #00f0ff;
  --neon-purple: #a855f7;
  --neon-green: #39ff14;
}
```

### Texte du Hero
Modifier dans `index.php`, section `.hero-content`.

### Coordonnées
Footer dans `index.php`, section `#contact`.

## 🔒 Sécurité

- Mots de passe hashés bcrypt
- Protection CSRF sur tous les formulaires
- Sessions sécurisées (httponly, samesite)
- Validation stricte uploads (whitelist MIME)
- Prepared statements PDO (SQL injection)
- Échappement XSS systématique (`htmlspecialchars`)
- `.htaccess` protège les uploads

## 🐛 Dépannage

### Erreur "Failed to open stream"
Vérifier les chemins absolus dans `require_once`.

### Erreur MySQL 1067
MySQL ne démarre pas : vérifier `C:\xampp\mysql\data\*.err` pour les logs.

### Upload échoue
- Vérifier `php.ini` : `upload_max_filesize` et `post_max_size`
- Droits en écriture sur `assets/uploads/`

### CSS/JS ne chargent pas
Vérifier les chemins relatifs depuis les pages admin (`../assets/`).

## 📝 TODO / Améliorations futures

- [ ] Génération automatique de thumbnails vidéo (ffmpeg)
- [ ] Drag-and-drop pour réorganiser projets
- [ ] Galerie lightbox pour prévisualisation
- [ ] Upload photo de profil admin
- [ ] Export/import projets (JSON)
- [ ] Analytics de vues par projet

## 📄 Licence

Portfolio personnel - Tokou Zime Maximilien © 2025

---

**Support**: tokoumaximilien@gmail.com  
**Localisation**: Cotonou, Bénin 🇧🇯
