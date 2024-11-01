<?php

namespace Controllers;

use Controllers\NutshellController;

class GravityFormsController
{
    public $nutshell;
    private static $instance;
    private $contacts = [];
    public $gf_data;

    private function __construct()
    {
        if (!$this->checkIfGFActive()) {
            return;
        }

        $this->nutshell->getInstanceData();
        $this->nutshell->getUser();
        $this->contacts = $this->nutshell->findNutshellContacts();
    }

    public function checkIfGFActive()
    {
        if (class_exists('GFCommon')) {
            $this->nutshell = new NutshellController();
            return true;
        }
        return false;
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function addContact($params)
    {
        $copy_params = $params;
        $new_contact = $this->nutshell->addContact($copy_params);

        return $new_contact;
    }

    public function addNote($params, $note)
    {
        $this->nutshell->addNote($params, $note);
    }

    public function editContact($params, $fields_to_update)
    {
        $params = (array) $params;
        $this->nutshell->editContact((array)$params, $fields_to_update);
    }

    public function getContact($contactID)
    {
        return $this->nutshell->getContact($contactID);
    }

    public function getMasterUser()
    {
        return $this->nutshell->getInstanceData();
    }

    public function findUsers($email='')
    {
        return $this->nutshell->findUsers($email);
    }

    public function findApiUsers($email='')
    {
        return $this->nutshell->findApiUsers($email);
    }

    public function searchContacts($name)
    {
        return $this->nutshell->searchContacts($name);
    }

    public function searchByEmail($email)
    {
        return $this->nutshell->searchByEmail($email);
    }

    public function getNutshellUser($userId = null, $rev= null)
    {
        return $this->nutshell->getUser($userId, $rev);
    }

    public function findTags()
    {
        return $this->nutshell->findTags();
    }

    public function newTag($tag)
    {
        return $this->nutshell->newTag($tag);
    }

    public function findCustomFields()
    {
        return $this->nutshell->findCustomFields();
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new GravityFormsController();
            return self::$instance;
        }
    }
}
