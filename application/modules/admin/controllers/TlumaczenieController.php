<?php

class Admin_TlumaczenieController extends kCMS_Admin
{
    public function preDispatch() {
        $this->view->controlname = "Tłumaczenie";
    }
// Pokaz wszystkie języki
    public function indexAction() {
        $db = Zend_Registry::get('db');
        $this->view->jezyk = $db->fetchAll($db->select()->from('tlumaczenie')->order('nazwa ASC'));
    }
// Dodaj nowy język
    public function dodajAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Nowy język";
        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/tlumaczenie/">Wróć do listy języków</a></div>';

        $form = new Form_JezykForm();
        $this->view->form = $form;
        $this->view->tinymce = "1";

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów $status, $kod, $nazwa, $flaga
            $formData = $this->_request->getPost();
            unset($formData['submit']);

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {
                $db->insert('tlumaczenie', $formData);
                $this->redirect('/admin/tlumaczenie/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }
// Edytuj język
    public function edytujAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/tlumaczenie/">Wróć do listy języków</a></div>';

        $form = new Form_JezykForm();
        $this->view->form = $form;
        $this->view->tinymce = "1";

        // Odczytanie id
        $id = (int)$this->getRequest()->getParam('id');
        $jezyk = $db->fetchRow($db->select()->from('tlumaczenie')->where('id = ?', $id));
        $this->view->pagename = " - Edytuj język - ".$jezyk->nazwa;

        // Załadowanie do forma
        if($jezyk){
            $form->populate((array)$jezyk);
        }

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['submit']);

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $db->update('tlumaczenie', $formData, 'id = '.$id);
                $this->redirect('/admin/tlumaczenie/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Usun język
		public function usunAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$jezyk = $db->fetchRow($db->select()->from('tlumaczenie')->where('id = ?', $id));
			
			$where = $db->quoteInto('id = ?', $id);
			$db->delete('tlumaczenie', $where);
			
			$where2 = $db->quoteInto('lang = ?', $jezyk-kod);
			$db->delete('tlumaczenie_slownik', $where2);
			
			$this->redirect('/admin/tlumaczenie/');
		}

################################################ SŁOWNIK WYBRANEGO JĘZYKA ################################################

// Pokaz wszystkie tlumaczenia
		public function pokazAction() {
			$db = Zend_Registry::get('db');
			$lang = $this->view->lang = $this->getRequest()->getParam('lang');
			$this->view->slownik = $db->fetchAll($db->select()->from('tlumaczenie_slownik')->where('lang = ?', $lang));
			$jezyk = $db->fetchRow($db->select()->from('tlumaczenie')->where('kod = ?', $lang));
			
			$this->view->pagename = "Przeglądaj słownik: ".$jezyk->nazwa;
		}
// Dodaj nowe tlumaczenie
		public function dodajWpisAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Nowe tlumaczenie";
			$lang = $this->getRequest()->getParam('lang');
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/tlumaczenie/pokaz/lang/'.$lang.'/">Wróć do słownika</a></div>';

			$form = new Form_SlownikForm();
			$this->view->form = $form;

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów $status, $kod, $nazwa, $flaga
					$keyword = $this->_request->getPost('keyword');
					$tlumaczenie = $this->_request->getPost('tlumaczenie');
					$formData = $this->_request->getPost();

					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {

					$select = $db->select()
					->from('tlumaczenie_slownik')
					->where('lang =?', $lang)
					->where('keyword =?', $keyword);
					$result = $db->fetchAll($select);

							if($result) {
								$data = array('word' => $tlumaczenie);
								$where = array(
									'keyword = ?' => $keyword,
									'lang = ?' => $lang
								);
								$db->update('tlumaczenie_slownik', $data, $where);
							} else {

								$data = array(
									'keyword' => $keyword,
									'word' => $tlumaczenie,
									'lang' => $lang
								);
								$db->insert('tlumaczenie_slownik', $data);
							}
					$this->_redirect('/admin/tlumaczenie/pokaz/lang/'.$lang.'/');

				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}
// Edytuj tlumaczenie
		public function edytujWpisAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Edytuj tłumaczenie";
			$lang = $this->getRequest()->getParam('lang');
			$id = (int)$this->getRequest()->getParam('id');
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/tlumaczenie/pokaz/lang/'.$lang.'/">Wróć do słownika</a></div>';

			$form = new Form_SlownikForm();
			$this->view->form = $form;
			$form->keyword->setAttribs(array('disable' => 'disable'));

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

			// Odczytanie id
			$jezyk = $db->fetchRow($db->select()->from('tlumaczenie_slownik')->where('id = ?', $id));

			// Załadowanie do forma $status, $kod, $nazwa, $flaga
			$form->keyword->setvalue($jezyk->keyword);
			$form->tlumaczenie->setvalue($jezyk->word);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów
					$tlumaczenie = $this->_request->getPost('tlumaczenie');
					$formData = $this->_request->getPost();

					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {

					//Pomyslnie
					$data = array('word' => $tlumaczenie);
					$db->update('tlumaczenie_slownik', $data, 'id = '.$id);
					$this->_redirect('/admin/tlumaczenie/pokaz/lang/'.$lang.'/');

				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}
}