<?php

class PersonnageManager
{
    private $_db;

    public function __construct($db)
    {
        $this->setDb($db);
    }

    public function add(Personnage $perso)
    {
        $q = $this->_db->prepare('INSERT INTO personnages SET nom = :nom, forcePerso = :forcePerso, degats = :degats, niveau = :niveau, experience = :experience');
        $q->bindValue(':nom', $perso->nom());
        $q->bindValue(':forcePerso', 1, PDO::PARAM_INT);
        $q->bindValue(':degats', 0, PDO::PARAM_INT);
        $q->bindValue(':niveau', 1, PDO::PARAM_INT);
        $q->bindValue(':experience', 0, PDO::PARAM_INT);
        $q->execute();
        $data_hydrate = $this->get(intval($this->_db->lastInsertId()));

        $perso->hydrate(array(
            'id' => $this->_db->lastInsertId(),
            'degats' => $data_hydrate->degats(),
            'niveau' => $data_hydrate->niveau(),
            'forcePerso' => $data_hydrate->forcePerso(),
            'experience' => $data_hydrate->experience(),
            'dernierCoup' => $data_hydrate->dernierCoup(),
            'nombreCoup' => $data_hydrate->nombreCoup(),
        ));
    }

    public function addCoup(Personnage $perso, $idpersonnage_frappe)
    {
        $q = $this->_db->prepare('INSERT INTO coups_personnages SET idpersonnage = :idpersonnage, idpersonnage_frapper = :idpersonnage_frapper, degats = :degats, niveau = :niveau, experience = :experience, date_coup = :date_coup');
        $q->bindValue(':idpersonnage', $perso->id());
        $q->bindValue(':idpersonnage_frapper', $idpersonnage_frappe, PDO::PARAM_INT);
        $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
        $q->bindValue(':niveau', $perso->niveau(), PDO::PARAM_INT);
        $q->bindValue(':experience', $perso->experience(), PDO::PARAM_INT);
        $q->bindValue(':date_coup', date('Y-m-d'), PDO::PARAM_STR);
        $q->execute();
    }

    public function count()
    {
        return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
    }

    public function delete(Personnage $perso)
    {
        $this->_db->exec('DELETE FROM personnages WHERE id = ' . $perso->id());
    }

    public function exists($info)
    {
        if (is_int($info)) {
            return (bool) $this->_db->query('SELECT COUNT(*) FROM personnages WHERE id = ' . $info)->fetchColumn();
        }

        $q = $this->_db->prepare('SELECT COUNT(*) FROM personnages WHERE nom = :nom');
        $q->execute(array(':nom' => $info));

        return (bool) $q->fetchColumn();
    }

    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->_db->query('SELECT * FROM personnages WHERE id = ' . $info);
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
        } else {
            $q = $this->_db->prepare('SELECT * FROM personnages WHERE nom = :nom');
            $q->execute(array(':nom' => $info));
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
        }

        if (!empty($donnees)) {
            $perso = new Personnage($donnees);
            if (!empty($donnees['id'])) {
                $last_coups = $this->getLastCoup($perso);
                if (!empty($last_coups)) {
                    $donnees['dernierCoup'] = count($this->getLastCoup($perso));
                    $donnees['nombreCoup'] = count($this->getCoup($perso));
                } else {
                    $donnees['dernierCoup'] = 0;
                    $donnees['nombreCoup'] = 0;
                }
            }
            $perso = new Personnage($donnees);
            return $perso;
        }
    }

    public function getLastCoup(Personnage $perso)
    {
        $q = $this->_db->prepare('SELECT * FROM coups_personnages WHERE idpersonnage = :idpersonnage AND date_coup = :date_coup ORDER BY idcoup_personnage');
        $q->execute(array(':idpersonnage' => $perso->id(), 'date_coup' => date('Y-m-d')));
        $result = array();
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $donnees;
        }
        return $result;
    }

    public function getCoup(Personnage $perso)
    {
        $q = $this->_db->prepare('SELECT * FROM coups_personnages WHERE idpersonnage = :idpersonnage ORDER BY idcoup_personnage');
        $q->execute(array(':idpersonnage' => $perso->id()));
        $result = array();
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $donnees;
        }
        return $result;
    }

    public function getList($nom)
    {
        $persos = array();
        $q = $this->_db->prepare('SELECT * FROM personnages WHERE nom <> :nom ORDER BY nom');
        $q->execute(array(':nom' => $nom));
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $persos[] = new Personnage($donnees);
        }
        return $persos;
    }

    public function update(Personnage $perso)
    {
        $q = $this->_db->prepare('UPDATE personnages SET forcePerso = :forcePerso, degats = :degats, niveau = :niveau, experience = :experience, derniereConnexion=:derniereConnexion WHERE id = :id');
        $q->bindValue(':forcePerso', $perso->forcePerso(), PDO::PARAM_INT);
        $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
        $q->bindValue(':niveau', $perso->niveau(), PDO::PARAM_INT);
        $q->bindValue(':experience', $perso->experience(), PDO::PARAM_INT);
        $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
        $q->bindValue(':derniereConnexion', $perso->derniereConnexion(), PDO::PARAM_STR);
        $q->execute();
    }

    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
}
