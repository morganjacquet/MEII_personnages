<?php

class Personnage
{
    private $_id;
    private $_nom;
    private $_forcePerso;
    private $_degats;
    private $_niveau;
    private $_experience;
    private $_dernierCoup;
    private $_nombreCoup;
    private $_derniereConnexion;

    const CEST_MOI = 1;
    const PERSONNAGE_TUE = 2;
    const PERSONNAGE_FRAPPE = 3;
    const PERSONNAGE_EPUISE = 4;

    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

    public function id()
    {
        return $this->_id;
    }

    public function nom()
    {
        return $this->_nom;
    }

    public function nomValide()
    {
        return !empty($this->_nom);
    }

    public function forcePerso()
    {
        return $this->_forcePerso;
    }

    public function degats()
    {
        return $this->_degats;
    }

    public function niveau()
    {
        return $this->_niveau;
    }

    public function experience()
    {
        return $this->_experience;
    }

    public function dernierCoup()
    {
        return $this->_dernierCoup;
    }

    public function nombreCoup()
    {
        return $this->_nombreCoup;
    }

    public function derniereConnexion()
    {
        return $this->_derniereConnexion;
    }

    public function verifDerniereConnexion()
    {
        $date = $this->derniereConnexion();
        $timestamp = strtotime($date);
        $cDate = strtotime(date('Y-m-d H:i:s'));
        $oldDate = $timestamp + 86400;

        if ($oldDate < $cDate) {
            if ($this->degats() - 10 > 0) {
                $this->_degats -= 10;
            } else {
                $this->_degats = 0;
            }
            $this->setDerniereConnexion(date("Y-m-d H:i:s"));
        }
    }

    public function frapper(Personnage $perso)
    {
        if ($perso->id() == $this->_id) {
            return self::CEST_MOI;
        }

        if ($this->_dernierCoup >= 3) {
            return self::PERSONNAGE_EPUISE;
        }

        $this->_dernierCoup++;
        $this->_nombreCoup++;

        return $perso->recevoirDegats($this);
    }

    public function setId($id)
    {
        $this->_id = (int) $id;
    }

    public function setNom($nom)
    {
        if (is_string($nom) && strlen($nom) <= 30) {
            $this->_nom = $nom;
        }
    }

    public function setForcePerso($forcePerso)
    {
        $forcePerso = (int) $forcePerso;
        if ($forcePerso >= 0 && $forcePerso <= 100) {
            $this->_forcePerso = $forcePerso;
        }
    }

    public function setDernierCoup($dernierCoup)
    {
        $this->_dernierCoup = $dernierCoup;
    }

    public function setNombreCoup($nombreCoup)
    {
        $this->_nombreCoup = $nombreCoup;
    }

    public function setDegats($degats)
    {
        $degats = (int) $degats;

        if ($degats >= 0 && $degats <= 100) {
            $this->_degats = $degats;
        }
    }

    public function setNiveau($niveau)
    {
        $niveau = (int) $niveau;
        if ($niveau >= 0) {
            $this->_niveau = $niveau;
        }
    }

    public function setExperience($exp)
    {
        $exp = (int) $exp;
        if ($exp >= 0 && $exp <= 100) {
            $this->_experience = $exp;
        }
    }

    public function setDerniereConnexion($derniereConnexion)
    {
        $this->_derniereConnexion = $derniereConnexion;
    }

    public function gagnerExperience($tuer = false)
    {
        if ($tuer) {
            $this->_experience += 20;
        } else {
            $this->_experience++;
        }

        if ($this->_experience >= 100) {
            $this->_niveau++;
            $this->gagnerForce();
            $this->_experience = 1;
        }
    }

    public function gagnerForce()
    {
        if ($this->forcePerso() < 100) {
            $this->_forcePerso = $this->_niveau * 2;
        }
    }

    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function recevoirDegats(Personnage $persoFrapper)
    {
        $this->_degats += $persoFrapper->forcePerso();
        if ($this->_degats >= 100) {
            $persoFrapper->gagnerExperience(true);
            return self::PERSONNAGE_TUE;
        } else {
            $persoFrapper->gagnerExperience();
            return self::PERSONNAGE_FRAPPE;
        }
    }
}
