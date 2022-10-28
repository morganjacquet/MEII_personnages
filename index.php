<?php

function chargerClasse($classe)
{
    require $classe . '.php';
}
spl_autoload_register('chargerClasse');


$db = new PDO('mysql:host=localhost;dbname=MII_personnage', 'root', 'root');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$manager = new PersonnageManager($db);

session_start();

if (isset($_GET['deconnexion'])) {
    session_destroy();
    header('Location: .');
    exit();
}

if (isset($_SESSION['perso'])) {
    $perso = $_SESSION['perso'];
    // $perso = $manager->get($perso->id());
}

if (isset($_POST['creer']) && isset($_POST['nom'])) {
    $perso = new Personnage(array('nom' => $_POST['nom']));
    if (!$perso->nomValide()) {
        $message = 'Le nom choisi est invalide.';
        unset($perso);
    } elseif ($manager->exists($perso->nom())) {
        $message = 'Le nom du personnage est déjà pris.';
        unset($perso);
    } else {
        $manager->add($perso);
    }
} elseif (isset($_POST['utiliser']) && isset($_POST['nom'])) {
    if ($manager->exists($_POST['nom'])) {
        $perso = $manager->get($_POST['nom']);
        $perso->verifDerniereConnexion();
        $manager->update($perso);
    } else {
        $message = 'Ce personnage n\'existe pas !';
    }
} elseif (isset($_GET['frapper'])) {
    if (!isset($perso)) {
        $message = 'Merci de créer un personnage ou de vous identifier.';
    } else {
        if (!$manager->exists((int) $_GET['frapper'])) {
            $message = 'Le personnage que vous voulez frapper n\'existe pas !';
        } else {
            $persoAFrapper = $manager->get((int) $_GET['frapper']);
            $retour = $perso->frapper($persoAFrapper);
            switch ($retour) {
                case Personnage::CEST_MOI :
                    $message = 'Mais... pourquoi voulez-vous vous frapper ???';
                    break;
                case Personnage::PERSONNAGE_FRAPPE :
                    $message = 'Le personnage a bien été frappé !';
                    $manager->update($perso);
                    $manager->addCoup($perso, $persoAFrapper->id());
                    $manager->update($persoAFrapper);
                    break;
                case Personnage::PERSONNAGE_TUE :
                    $message = 'Vous avez tué ce personnage !';
                    $manager->update($perso);
                    $manager->delete($persoAFrapper);
                    break;
                case Personnage::PERSONNAGE_EPUISE :
                    $message = 'Vous avez trop frapper aujourd\'hui !';
                    $manager->update($perso);
                    break;
            }
        }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
<title>TP : Mini jeu de combat</title>
<meta http-equiv="Content-type" content="text/html; charset=iso-
8859-1" />
</head>
<body>
<p>Nombre de personnages créés : <?php echo $manager->count(); ?></p>
<?php
if (isset($message)) {
    echo '<p>', $message, '</p>';
}

if (isset($perso)) {
?>
<p><a href="?deconnexion=1">Déconnexion</a></p>
<fieldset>
<legend>Mes informations</legend>
<p>
    Nom : <?php echo htmlspecialchars($perso->nom()); ?><br />
    Dégâts : <?php echo $perso->degats(); ?>  |  Niveau : <?php echo htmlspecialchars($perso->niveau());?> |  Expérience : <?php echo htmlspecialchars($perso->experience()); ?> |  Force : <?php echo htmlspecialchars($perso->forcePerso()); ?> |  Nombre de coup : <?php echo htmlspecialchars($perso->nombreCoup()); ?> (<?php echo htmlspecialchars($perso->dernierCoup()); ?> aujourd'hui)
</p>
</fieldset>
<fieldset>
<legend>Qui frapper ?</legend>
<p>
<?php
$persos = $manager->getList($perso->nom());

if (empty($persos)) {
    echo 'Personne à frapper !';
} else {
    foreach ($persos as $unPerso) {
        echo '<a href="?frapper=', $unPerso->id(), '">',
        htmlspecialchars($unPerso->nom()), '</a> (Dégâts : ', $unPerso->degats(), ' | Expérience : ', $unPerso->experience(), ' | Niveau : ', $unPerso->niveau(), ' | Force : ', $unPerso->forcePerso(), ')<br />';
    }
}
?>
</p>
</fieldset>
<?php
} else {
?>
<form action="" method="post">
<p>
Nom : <input type="text" name="nom" maxlength="50" /> <input type="submit" value="Créer ce personnage"
name="creer" />
<input type="submit" value="Utiliser ce personnage"
name="utiliser" />
</p>
</form>
<?php
}
?>
</body>
</html>
<?php
if (isset($perso)) {
    $_SESSION['perso'] = $perso;
}
?>