<?php
class Admin_OfertaController extends kCMS_Admin
{
    private $redirect;
    private $table;
    private $Image;
    private $Translate;
    private $Form;

    public function preDispatch() {
        $this->Image= new Model_OfferModel();
        $this->Translate = new Model_TranslateModel();
        $this->Form = new Form_NazwaPlikTekstForm();
        $this->table = 'oferta';
        $back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/oferta/">Wróć do listy</a></div>';
        $this->redirect = 'admin/oferta';
        $array = array(
            'controlname' => "Nasza oferta",
            'back' => $back,
            'tinymce' => 1
        );
        $this->view->assign($array);
    }

// Pokaz wszystkie
    public function indexAction() {
        $array = array(
            'lista' => $this->Image->get()
        );
        $this->view->assign($array);
    }

// Nowy wpis
    function addAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Dodaj ofertę";

        $this->view->form = $this->Form;

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['submit']);

            //Sprawdzenie poprawnosci forma
            if ($this->Form->isValid($formData)) {
                $db->insert($this->table, $formData);
                $lastId = $db->lastInsertId();
                $this->redirect($this->redirect);
            } else {
                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
            }
        }
    }

// Edytuj wpis
    function editAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);

        // Odczytanie id
        $id = (int)$this->_request->getParam('id');
        $entry = $db->fetchRow($db->select()->from($this->table)->where('id = ?', $id));

        $this->view->pagename = " - Edytuj: ".$entry->nazwa;
        $this->view->form = $this->Form;

        // Załadowanie do forma
        $array = json_decode(json_encode($entry), true);
        if($array){
            $this->Form->populate($array);
        }

        if ($this->_request->isPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['submit']);

            //Sprawdzenie poprawnosci forma
            if ($this->Form->isValid($formData)) {

                $db->update($this->table, $formData, 'id = '.$id);
                $this->redirect($this->redirect);
            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';

            }
        }
    }

// Edytuj języki
    public function tlumaczenieAction() {
        $this->_helper->viewRenderer('form', null, true);

        // Odczytanie id
        $id = (int)$this->getRequest()->getParam('id');
        $lang = $this->getRequest()->getParam('lang');
        if(!$id || !$lang){
            $this->redirect($this->redirect);
        }
        $entry = $this->Image->find($id)->current();
        $tlumaczenie = $this->Translate->getTranslate($this->Image->_module, $id, $lang);

        // Laduj form
        $array = array(
            'form' => $this->Form,
            'back' => '<div class="back"><a href="'.$this->view->baseUrl().'/admin/atuty/">Wróć do listy</a></div>',
            'pagename' => ' - Edytuj tłumaczenie: '.$entry->nazwa
        );
        $this->view->assign($array);

        if($tlumaczenie) {
            $arrayForm = json_decode($tlumaczenie->json, true);
            $this->Form->populate($arrayForm);
        }

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            $formData = $this->_request->getPost();

            //Sprawdzenie poprawnosci forma
            if ($this->Form->isValid($formData)) {

                $this->Translate->saveTranslate($formData, $this->Image->_module, $entry->id, $lang);
                $this->redirect($this->redirect);

            }
        }
    }

// Usuń panel
    function deleteAction() {
        $db = Zend_Registry::get('db');
        // Odczytanie id obrazka
        $id = (int)$this->_request->getParam('id');
        $where = $db->quoteInto('id = ?', $id);
        $db->delete($this->table, $where);

        $this->redirect($this->redirect);
    }

// Ustaw kolejność
    public function sortAction() {
        $db = Zend_Registry::get('db');
        $updateRecordsArray = $_POST['recordsArray'];
        $listingCounter = 1;
        foreach ($updateRecordsArray as $recordIDValue) {
            $data = array('sort' => $listingCounter);
            $db->update($this->table, $data, 'id = '.$recordIDValue);
            $listingCounter = $listingCounter + 1;
        }
    }
}