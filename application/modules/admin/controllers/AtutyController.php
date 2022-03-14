<?php
require_once 'kCMS/Thumbs/ThumbLib.inc.php';
class Admin_AtutyController extends kCMS_Admin
{
    private $redirect;
    private $table;
    private $Image;
    private $Translate;
    private $Form;

    public function preDispatch() {
        $this->Image= new Model_ImageModel();
        $this->Translate = new Model_TranslateModel();
        $this->Form = new Form_NazwaPlikLinkForm();
        $this->table = 'image';
        $back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/atuty/">Wróć do listy</a></div>';
        $info = '<div class="info">Obrazek o wymiarach: szerokość <b>120</b>px / wysokość <b>120</b>px</div>';
        $this->redirect = 'admin/atuty';
        $array = array(
            'controlname' => "Partnerzy",
            'back' => $back,
            'info' => $info,
        );
        $this->view->assign($array);
    }

// Pokaz wszystkie
    public function indexAction() {
        $array = array(
            'lista' => $this->Image->getImages(1)
        );
        $this->view->assign($array);
    }

// Nowy wpis
    function addAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Dodaj obrazek";

        $this->view->form = $this->Form;
        $this->Form->removeElement('link');

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['MAX_FILE_SIZE']);
            unset($formData['obrazek']);
            unset($formData['submit']);

            $obrazek = $_FILES['obrazek']['name'];
            if($_FILES['obrazek']['size'] > 0) {
                $plik = date('mdhis').'-'.slugImg($formData['nazwa'], $obrazek);
            }

            //Sprawdzenie poprawnosci forma
            if ($this->Form->isValid($formData)) {
                $formData['id_place'] = 1;

                $db->insert($this->table, $formData);
                $lastId = $db->lastInsertId();

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/images/'.$plik);
                    $upfile = FILES_PATH.'/images/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->resize(120, 120)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update($this->table, array('plik' => $plik), 'id = ' . $lastId);
                }

                $this->_redirect($this->redirect);
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
        $this->Form->removeElement('link');

        // Załadowanie do forma
        $array = json_decode(json_encode($entry), true);
        if($array){
            $this->Form->populate($array);
        }

        if ($this->_request->isPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['MAX_FILE_SIZE']);
            unset($formData['obrazek']);
            unset($formData['submit']);

            $obrazek = $_FILES['obrazek']['name'];
            if($_FILES['obrazek']['size'] > 0) {
                $plik = date('mdhis').'-'.slugImg($formData['nazwa'], $obrazek);
            }

            //Sprawdzenie poprawnosci forma
            if ($this->Form->isValid($formData)) {

                $db->update($this->table, $formData, 'id = '.$id);

                if($_FILES['obrazek']['size'] > 0) {
                    unlink(FILES_PATH."/images/".$entry->plik);

                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/images/'.$plik);
                    $upfile = FILES_PATH.'/images/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->resize(120, 120)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update($this->table, array('plik' => $plik), 'id = ' . $id);
                }

                $this->_redirect($this->redirect);
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
            $this->_redirect($this->redirect);
        }
        $entry = $this->Image->find($id)->current();
        $tlumaczenie = $this->Translate->getTranslate($this->Image->_module, $id, $lang);

        // Laduj form
        $this->Form->removeElement('obrazek');
        $this->Form->removeElement('link');

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
                $this->_redirect($this->redirect);

            }
        }
    }

// Usuń panel
    function deleteAction() {
        $db = Zend_Registry::get('db');
        // Odczytanie id obrazka
        $id = (int)$this->_request->getParam('id');
        $entry = $db->fetchRow($db->select()->from($this->table)->where('id = ?', $id));

        unlink(FILES_PATH."/images/".$entry->plik);

        $where = $db->quoteInto('id = ?', $id);
        $db->delete($this->table, $where);

        $this->_redirect($this->redirect);
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