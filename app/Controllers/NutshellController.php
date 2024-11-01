<?php

namespace Controllers;

use GravityFormsController;

require_once(dirname(__FILE__) .'/../../vendor/NutshellApi.php');

class NutshellController
{
    public $api;
    private $id;
    private $user;
    private $contacts = [];

    public function __construct()
    {
        $username = $apiKey = '';
        $username = get_option('nutshell_api_username');
        $apiKey = get_option('nutshell_api_key');

        if ($username && $apiKey) {
            try {
                $this->api = new \NutshellApi($username, $apiKey);
            } catch (NutshellApiException $e) {
                $this->addMessage($e);
                return;
            }
        }
    }

    public function getInstanceData()
    {
        if ($this->api) {
            try {
                $this->id = $this->api->instanceData()->id;
                return $this->id;
            } catch (NutshellApiException $e) {
                $this->addMessage($e);
                return;
            }
        }
    }

    public function addMessage(NutshellApiException $e)
    {
        global $error;
        global $err_message;

        $error = true;

        $err_message = '';
        $err_message=$e->getMessage();
    }

    public function getUser()
    {
        if ($this->api) {
            try {
                $this->user = $this->api->getUser($this->id);
                return $this->user;
            } catch (\NutshellApiException $e) {
            }
        }
    }

    public function getNote()
    {
        $this->result = $this->api->call('getNote', array($userId));
    }

    public function getContact($id)
    {
        $contact = $this->api->getContact(['contactId' => $id]);
        return $contact;
    }

    public function findNutshellContacts()
    {
        if ($this->api) {
            try {
                $params = array('contactId' => $this->id, 'orderBy' => 'modifiedTime');
                $result = $this->api->findContacts($params);
                return $result;
            } catch (\NutshellApiException $e) {
            }
        }
    }

    public function getAuthenticatedUser()
    {
        try {
            return $this->user;
        } catch (\NutshellApiException $e) {
        }
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function addContact($params)
    {
        $newContact = $this->api->newContact($params);

        $newContactId = $newContact->id;

        if ($newContactId) {
            return $newContactId;
        }
        return false;
    }

    public function addNote($params, $note)
    {
        $entity = $params['entity'];
        $newNote = $this->api->newNote($entity, $note);
    }

    public function editContact($params, $fields_to_update)
    {
        $this->api->editContact($params['id'], $params['rev'][0], $fields_to_update);
    }

    public function findUsers($email)
    {
        return $this->api->searchContactsAndUsers(['string' => $email, 'stubResponses' => false ]);
    }

    public function findApiUsers($email='')
    {
        return $this->api->findUsers(['string' => $email, 'stubResponses' => false ]);
    }

    public function searchContacts($name)
    {
        return $this->api->searchContacts(['string' => $name]);
    }

    public function searchByEmail($email)
    {
        return $this->api->searchByEmail($email);
    }

    public function getNutshellUser($userId, $rev)
    {
        return $this->api->getUser($userId, $rev);
    }

    public function findTags()
    {
        if ($this->api) {
            try {
                return $this->api->findTags();
            } catch (\NutshellApiException $e) {}
        }
    }

    public function newTag($tag)
    {
        if ($this->api) {
            try {
                return $this->api->newTag($tag);
            } catch (\NutshellApiException $e) {}
        }
    }

    public function findCustomFields()
    {
        if ($this->api) {
            try {
                return $this->api->findCustomFields();
            } catch (\NutshellApiException $e) {
            }
        }
    }
}
