<?php
require_once 'kCMS/Thumbs/ThumbLib.inc.php';
class Admin_GaleriaController extends kCMS_Admin
{
        private $redirect;
        private $Translate;
        private $Form;
        private $Model;

		public function preDispatch() {
			$this->view->controlname = "Galeria";

            $this->Model = new Model_GalleryModel();
            $this->Form = new Form_NazwaForm();

            $this->Translate = new Model_TranslateModel();
            $this->redirect = 'admin/galeria';
		}
################################################ ZDJĘCIA PRODUKTÓW ################################################

// Pokaz wszystkie galerie
		public function indexAction() {
			$db = Zend_Registry::get('db');
			$this->view->katalog = $db->fetchAll($db->select()->from('galeria')->order('sort ASC'));
		}

// Pokaz wszystkie zdjecia wybranej galerii
		public function pokazAction() {
			$db = Zend_Registry::get('db');
			$this->view->kat = $id = (int)$this->getRequest()->getParam('id');
			$this->view->katalog = $db->fetchRow($db->select()->from('galeria')->where('id =?', $id));
			$this->view->zdjecia = $db->fetchAll($db->select()->from('galeria_zdjecia')->order('sort ASC')->where('id_gal =?', $id));
		}

// Dodaj galerie
		public function nowaAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Nowa galeria";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/galeria/">Wróć do listy galerii</a></div>';
			
			$form = new Form_GaleriaForm();
			$this->view->form = $form;

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
					if ($form->isValid($formData)) {

                        $formData['slug'] = slug($formData['nazwa']);
                        $db->insert('galeria', $formData);
                        $lastId = $db->lastInsertId();

                        if($_FILES['obrazek']['size'] > 0) {
                            move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/galeria/catalog/'.$plik);
                            $upfile = FILES_PATH.'/galeria/catalog/'.$plik;
                            chmod($upfile, 0755);

                            PhpThumbFactory::create($upfile)
                                ->adaptiveResizeQuadrant(570, 321)
                                ->save($upfile);
                            chmod($upfile, 0755);

                            $db->update('galeria', array('plik' => $plik), 'id = ' . $lastId);
                        }

						$this->redirect('/admin/galeria/');
				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}

// Edytuj galerie
		public function edytujAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Edytuj galerię";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/galeria/">Wróć do listy galerii</a></div>';
			
			$form = new Form_GaleriaForm();
			$this->view->form = $form;

			// Odczytanie id
			$id = (int)$this->getRequest()->getParam('id');
            $entry = $db->fetchRow($db->select()->from('galeria')->where('id = ?',$id));

			// Załadowanie do forma
            $array = json_decode(json_encode($entry), true);
            if($array){
                $form->populate($array);
            }

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
					if ($form->isValid($formData)) {

                        $formData['slug'] = slug($formData['nazwa']);
                        $db->update('galeria', $formData, 'id = '.$id);

                        if($_FILES['obrazek']['size'] > 0) {
                            unlink(FILES_PATH."/galeria/catalog/".$entry->plik);

                            move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/galeria/catalog/'.$plik);
                            $upfile = FILES_PATH.'/galeria/catalog/'.$plik;
                            chmod($upfile, 0755);

                            PhpThumbFactory::create($upfile)
                                ->adaptiveResizeQuadrant(570, 428)
                                ->save($upfile);
                            chmod($upfile, 0755);

                            $db->update('galeria', array('plik' => $plik), 'id = ' . $id);
                        }

						$this->redirect('/admin/galeria/');
						
				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}

// Usun galerie
		public function usunKatalogAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$where = $db->quoteInto('id = ?', $id);
			$count = $db->fetchAll($db->select()->from('galeria_zdjecia')->where('id_gal = ?',$id));
			foreach($count as $element) {

                unlink(FILES_PATH."/galeria/".$element->plik);
                unlink(FILES_PATH."/galeria/thumbs/".$element->plik);

				$where2 = $db->quoteInto('id = ?', $element->id);
				$db->delete('galeria_zdjecia', $where2);
			}

			$db->delete('galeria', $where);
			$this->redirect('/admin/galeria/');
		}

// Ustaw kolejność
		public function ustawAction() {
			$db = Zend_Registry::get('db');
			$tabela = $this->_request->getParam('co');
			$updateRecordsArray = $_POST['recordsArray'];
			$listingCounter = 1;
			foreach ($updateRecordsArray as $recordIDValue) {
				$data = array('sort' => $listingCounter);
				$db->update($tabela, $data, 'id = '.$recordIDValue);
				$listingCounter = $listingCounter + 1;
				}
		}
	
################################################ PRODUKTY/ZDJĘCIA ################################################

// Upload obrazka
		public function uploadAction() {
			$this->_helper->layout()->disableLayout(); 
			$this->_helper->viewRenderer->setNoRender(true);
			$id = (int)$this->getRequest()->getParam('id');

			$db = Zend_Registry::get('db');

            $katalog = $db->fetchRow($db->select()->from('galeria')->where('id = ?',$id));

			$obrazek = $_FILES['qqfile']['name'];
            if($_FILES['qqfile']['size'] > 0) {
                $plik = time()."-".rand(1000, 9999)."-".slugImg($katalog->nazwa, $obrazek);
            }

			if (move_uploaded_file($_FILES['qqfile']['tmp_name'], FILES_PATH.'/galeria/big/'.$plik)) {
				$upfile = FILES_PATH.'/galeria/big/'.$plik;
				$thumbs = FILES_PATH.'/galeria/thumbs/'.$plik;
				chmod($upfile, 0755);

				$data = array('plik' => $plik, 'id_gal' => $id, 'nazwa' => $katalog->nazwa);

                $options = array('jpegQuality' => 80);
                $options2 = array('jpegQuality' => 60);

				PhpThumbFactory::create($upfile, $options)
                    ->resize(1170, 1170)
                    ->save($upfile);

				PhpThumbFactory::create($upfile, $options2)
                    ->adaptiveResizeQuadrant(540, 405, 'B')
                    ->save($thumbs);

				$db->insert('galeria_zdjecia', $data);

                function watermark_image($target, $wtrmrk_file, $newcopy) {
                    $watermark = imagecreatefrompng($wtrmrk_file);
                    imagealphablending($watermark, false);
                    imagesavealpha($watermark, true);
                    $img = imagecreatefromjpeg($target);
                    $img_w = imagesx($img);
                    $img_h = imagesy($img);
                    $wtrmrk_w = imagesx($watermark);
                    $wtrmrk_h = imagesy($watermark);
                    $dst_x = ($img_w / 2) - ($wtrmrk_w / 2); // For centering the watermark on any image
                    $dst_y = ($img_h / 2) - ($wtrmrk_h / 2); // For centering the watermark on any image
                    imagecopy($img, $watermark, $dst_x, $dst_y, 0, 0, $wtrmrk_w, $wtrmrk_h);
                    imagejpeg($img, $newcopy, 100);
                    imagedestroy($img);
                    imagedestroy($watermark);
                }

                watermark_image($upfile,FILES_PATH.'/galeria/watermark.png', $upfile);

				$response = array("success" => true);
				header("Content-Type: text/plain");
				echo Zend_Json::encode($response);
			}
		}

// Usun zdjecie
		public function usunObrazekAction() {
			$db = Zend_Registry::get('db');

			// Odczytanie id obrazka
			$id = (int)$this->getRequest()->getParam('id');
			$pic = $db->fetchRow($db->select()->from('galeria_zdjecia')->where('id = ?',$id));
			
			unlink(FILES_PATH."/galeria/".$pic->plik);
			unlink(FILES_PATH."/galeria/thumbs/".$pic->plik);

			$where = $db->quoteInto('id = ?', $id);
			$db->delete('galeria_zdjecia', $where);
			$this->redirect('/admin/galeria/pokaz/id/'.$pic->id_gal.'/');
		}

// Usun kilka zdjęć
		public function kilkaAction() {
			$db = Zend_Registry::get('db');
			$checkbox = $_POST[checkbox];
			for($i=0;$i<count($_POST[checkbox]);$i++){
				$id = $checkbox[$i];
				$pic = $db->fetchRow($db->select()->from('galeria_zdjecia')->where('id = ?',$id));

				unlink(FILES_PATH."/galeria/big/".$pic->plik);
				unlink(FILES_PATH."/galeria/thumbs/".$pic->plik);

				$where = $db->quoteInto('id = ?', $id);
				$db->delete('galeria_zdjecia', $where);
			}
			$this->redirect('/admin/galeria/pokaz/id/'.$pic->id_gal.'/');
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
        $entry = $this->Model->find($id)->current();
        $tlumaczenie = $this->Translate->getTranslate($this->Model->_module, $id, $lang);

        // Laduj form

        $array = array(
            'form' => $this->Form,
            'back' => '<div class="back"><a href="'.$this->view->baseUrl().'/admin/galeria/">Wróć do listy</a></div>',
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

                $this->Translate->saveTranslate($formData, $this->Model->_module, $entry->id, $lang);
                $this->redirect($this->redirect);

            }
        }
    }
}