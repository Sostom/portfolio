<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Requête invalide.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $db = getDB();
            $stmt = $db->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $project = $stmt->fetch();

            if ($project) {
                // Supprimer fichiers associés
                if (!empty($project['thumbnail']) && file_exists(__DIR__ . '/../' . $project['thumbnail'])) {
                    @unlink(__DIR__ . '/../' . $project['thumbnail']);
                }
                if (!empty($project['media_file']) && file_exists(__DIR__ . '/../' . $project['media_file'])) {
                    @unlink(__DIR__ . '/../' . $project['media_file']);
                }

                $stmt = $db->prepare('DELETE FROM projects WHERE id = :id');
                $stmt->execute([':id' => $id]);
                set_flash('success', 'Projet supprimé.');
            }
        }
    }
}
header('Location: index.php');
exit;
