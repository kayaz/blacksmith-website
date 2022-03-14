<?php

class Admin_SekcjeController extends kCMS_Admin
{
		
		public function preDispatch() {
			$this->view->controlname = "Sekcje tekstowe";
			
			$this->sql_table = 'sekcje';
			$this->backto = '/admin/sekcje/';
		}
		
// Ustaw kolejność
		public function ustawAction() {
			$db = Zend_Registry::get('db');
			$updateRecordsArray = $_POST['recordsArray'];
			$listingCounter = 1;
			foreach ($updateRecordsArray as $recordIDValue) {
				$data = array('sort' => $listingCounter);
				$db->update($this->sql_table, $data, 'id = '.$recordIDValue);
				$listingCounter = $listingCounter + 1;
				}
		}
		
// Pokaz wszystkie wpisy
		public function indexAction() {
			$db = Zend_Registry::get('db');
			$this->view->lista = $db->fetchAll($db->select()->from($this->sql_table)->order('sort ASC'));
		}
		
// Dodaj nowy wpis
		public function atutAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Nowy atut";
			$this->view->tinymce = "1";

			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().$this->backto.'">Wróć do listy</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: szerokość <b>540 px</b> / wysokość <b>410 px</b></div>';

			$form = new Form_SekcjaForm();
			$this->view->form = $form;

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);
			
			$form->removeElement('link');
			$form->removeElement('link_button');
			$form->removeElement('link2');
			$form->removeElement('link_button2');

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					$formData = $this->_request->getPost();
					unset($formData['MAX_FILE_SIZE']);
					unset($formData['obrazek']);
					unset($formData['submit']);

					$obrazek = $_FILES['obrazek']['name'];
					$tag = zmiana(strip_tags($formData['tytul']));
					$tag = substr($tag,0,32);
					$uniqid = uniqid();
					$plik = $tag.'_'.$uniqid.'.'.zmiennazwe($obrazek);

					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {

						$db->insert($this->sql_table, $formData);
						$lastId = $db->lastInsertId();

						if ($obrazek) {

							move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/sekcje/'.$plik);
							$upfile = FILES_PATH.'/sekcje/'.$plik;
							chmod($upfile, 0755);
							
							require_once 'kCMS/Thumbs/ThumbLib.inc.php';

							$options = array('jpegQuality' => 95);
							$thumb = PhpThumbFactory::create($upfile)->adaptiveResizeQuadrant(540, 410)->save($upfile);

							$dataImg = array('plik' => $plik);
							$db->update($this->sql_table, $dataImg, 'id = '.$lastId);
							
						}

						$this->_redirect($this->backto);

				} else {

					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}
		
// Edytuj wpis
		public function edytujAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->tinymce = "1";
			
			$id = (int)$this->getRequest()->getParam('id');
			$wpis = $db->fetchRow($db->select()->from($this->sql_table)->where('id = ?', $id));
			
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().$this->backto.'">Wróć do listy</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: szerokość <b>540 px</b> / wysokość <b>410 px</b></div>';
			
			$form = new Form_SekcjaForm();
			$this->view->form = $form;

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

			$pola = explode(',', $wpis->pola);
			foreach($pola as $p){
				$form->removeElement($p);
			}


			// Odczytanie id
			$this->view->pagename = " - Edytuj sekcje: ".$wpis->nazwa;

			// Załadowanie do forma
			$array = json_decode(json_encode($wpis), true);
			$form->populate($array);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów
					$formData = $this->_request->getPost();
					unset($formData['MAX_FILE_SIZE']);
					unset($formData['obrazek']);
					unset($formData['submit']);
					$obrazek = $_FILES['obrazek']['name'];
					$tag = zmiana(strip_tags($formData['tytul']));
					$tag = substr($tag,0,32);
					$uniqid = uniqid();
					$plik = $tag.'_'.$uniqid.'.'.zmiennazwe($obrazek);

						//Sprawdzenie poprawnosci forma
						if ($form->isValid($formData)) {
						
							$db->update($this->sql_table, $formData, 'id = '.$id);

							if ($obrazek) {
								//Usuwanie starych zdjęć
								unlink(FILES_PATH."/sekcje/".$wpis->plik);
								
								move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/sekcje/'.$plik);
								$upfile = FILES_PATH.'/sekcje/'.$plik;
								chmod($upfile, 0755);
								
								require_once 'kCMS/Thumbs/ThumbLib.inc.php';

								$options = array('jpegQuality' => 95);
								$thumb = PhpThumbFactory::create($upfile)->adaptiveResizeQuadrant(540, 410)->save($upfile);

								$dataImg = array('plik' => $plik);
								$db->update($this->sql_table, $dataImg, 'id = '.$id);
								
							}
							
							$this->_redirect($this->backto);
							
						} else {
											
							//Wyswietl bledy	
							$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
							$form->populate($formData);

						}

			}
		}

// Usun wpis
		public function usunAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$wpis = $db->fetchRow($db->select()->from($this->sql_table)->where('id = ?', $id));
			
			unlink(FILES_PATH."/sekcje/".$wpis->plik);
			$db->delete($this->sql_table, 'id = '.$id);
			$this->_redirect($this->backto);
		}
}