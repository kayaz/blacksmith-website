<?php
require_once 'kCMS/Thumbs/ThumbLib.inc.php';
class Admin_SliderController extends kCMS_Admin
{
    private $redirect;
    private $table;
    private $form;
    private $Slider;

    public function preDispatch() {
            $this->Slider = new Model_SliderModel();
            $this->form = new Form_SliderForm();

            $back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/slider/">Wróć do listy paneli</a></div>';
            $info = '<div class="info">Obrazek o wymiarach: szerokość <b>'.$this->Slider::IMG_WIDTH.'</b>px / wysokość <b>'.$this->Slider::IMG_HEIGHT.'</b>px</div>';
			$this->redirect = 'admin/slider';
            $this->table = 'slider';
            $array = array(
                'controlname' => 'Slider',
                'back' => $back,
                'info' => $info,
            );
            $this->view->assign($array);
		}
		
// Pokaz wszystkie panele
		public function indexAction() {
            $array = array(
                'lista' => $this->Slider->fetchAll($this->Slider->select()->order('sort ASC'))
            );
            $this->view->assign($array);
		}

// Dodaj nowy panel
		function nowyAction() {
			$this->_helper->viewRenderer('form', null, true);

            $array = array(
                'form' => $this->form,
                'pagename' => " - Dodaj zdjecie"
            );
            $this->view->assign($array);

            //Akcja po wcisnieciu Submita
            if ($this->_request->getPost()) {

                //Odczytanie wartosci z inputów
                $formData = $this->_request->getPost();
                unset($formData['MAX_FILE_SIZE']);
                unset($formData['obrazek']);
                unset($formData['submit']);

                $obrazek = $_FILES['obrazek']['name'];
                if($_FILES['obrazek']['size'] > 0) {
                    $plik = slugImg($formData['tytul'], $obrazek);
                }
                //Sprawdzenie poprawnosci forma
                if ($this->form->isValid($formData)) {

                    $lastId = $this->Slider->insert($formData);

                    if($_FILES['obrazek']['size'] > 0) {
                        move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/slider/'.$plik);
                        $upfile = FILES_PATH.'/slider/'.$plik;
                        $thumb = FILES_PATH.'/slider/thumbs/'.$plik;
                        chmod($upfile, 0755);

                        $options = array('jpegQuality' => 90);
                        PhpThumbFactory::create($upfile, $options)
                        ->adaptiveResizeQuadrant($this->Slider::IMG_WIDTH, $this->Slider::IMG_HEIGHT)
                        ->save($upfile);

                        PhpThumbFactory::create($upfile, $options)
                            ->resize(159, 159)
                            ->save($thumb);
                        chmod($upfile, 0755);
                        chmod($thumb, 0755);

                        $dataImg = array('plik' => $plik);
                        $this->Slider->update($dataImg, 'id = ' . $lastId);
                    }

                    $this->redirect('/admin/slider/');
                } else {
                    //Wyswietl bledy
                    $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                }
            }
		}

// Edytuj panel
		function edytujAction() {
			$this->_helper->viewRenderer('form', null, true);

			// Odczytanie id
			$id = (int)$this->_request->getParam('id');
            $slider = $this->Slider->find($id)->current();

            $array = array(
                'form' => $this->form,
                'pagename' => " - Edytuj panel: ".$slider->tytul
            );
            $this->view->assign($array);

            // Załadowanie do forma
			if($array){
                $this->form->populate($slider->toArray());
			}

			if ($this->_request->isPost()) {

				//Odczytanie wartosci z inputów
				$formData = $this->_request->getPost();
				unset($formData['MAX_FILE_SIZE']);
				unset($formData['obrazek']);
				unset($formData['submit']);

                $obrazek = $_FILES['obrazek']['name'];
                if($_FILES['obrazek']['size'] > 0) {
                    $plik = slugImg($formData['tytul'], $obrazek);
                }

                //Sprawdzenie poprawnosci forma
                if ($this->form->isValid($formData)) {

                    $this->Slider->update($formData, 'id = '.$id);

                    if($_FILES['obrazek']['size'] > 0) {
                        unlink(FILES_PATH."/slider/".$slider->plik);
                        unlink(FILES_PATH."/slider/thumbs/".$slider->plik);

                        move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/slider/'.$plik);
                        $upfile = FILES_PATH.'/slider/'.$plik;
                        $thumb = FILES_PATH.'/slider/thumbs/'.$plik;
                        chmod($upfile, 0755);

                        $options = array('jpegQuality' => 90);
                        PhpThumbFactory::create($upfile, $options)
                        ->adaptiveResizeQuadrant($this->Slider::IMG_WIDTH, $this->Slider::IMG_HEIGHT)
                        ->save($upfile);

                        PhpThumbFactory::create($upfile, $options)
                        ->resize(159, 159)
                        ->save($thumb);
                        chmod($upfile, 0755);
                        chmod($thumb, 0755);

                        $dataImg = array('plik' => $plik);
                        $this->Slider->update($dataImg, 'id = ' . $id);
                    }

                    $this->redirect('/admin/slider/');
                } else {

                    //Wyswietl bledy
                    $this->view->message = '<div class="error">Formularz zawiera błędy</div>';

                }
			}
		}

// Edytuj języki
        public function tlumaczenieAction() {
            $db = Zend_Registry::get('db');
            $this->_helper->viewRenderer('form', null, true);

            // Odczytanie id
            $id = (int)$this->getRequest()->getParam('id');
            $lang = $this->getRequest()->getParam('lang');
            if(!$id || !$lang){
                $this->_redirect($this->redirect);
            }

            $wpis = $db->fetchRow($db->select()->from($this->table)->where('id = ?', $id));
            $tlumaczenieQuery = $db->select()
                ->from('tlumaczenie_wpisy')
                ->where('module = ?', 'slider')
                ->where('id_wpis = ?', $id)
                ->where('lang = ?', $lang);
            $tlumaczenie = $db->fetchRow($tlumaczenieQuery);

            // Laduj form
            $form = new Form_SliderForm();
            $this->view->form = $form;

            if($tlumaczenie) {
                $array = json_decode($tlumaczenie->json, true);
                $form->populate($array);
            }

            $this->view->pagename = " - Edytuj tłumaczenie: ".$wpis->tytul;
            $form->removeElement('obrazek');

            //Akcja po wcisnieciu Submita
            if ($this->_request->getPost()) {

                $formData = $this->_request->getPost();

                //Sprawdzenie poprawnosci forma
                if ($form->isValid($formData)) {

                    $translateModel = new Model_TranslateModel();
                    $translateModel->saveTranslate($formData, 'slider', $wpis->id, $lang);
                    $this->redirect($this->redirect);

                } else {

                    //Wyswietl bledy
                    $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                    $form->populate($formData);

                }
            }
        }

// Usuń panel
		function usunAction() {
            $id = (int)$this->_request->getParam('id');
            $slider = $this->Slider->find($id)->current();
			unlink(FILES_PATH."/slider/".$slider->plik);
			unlink(FILES_PATH."/slider/thumbs/".$slider->plik);
			$slider->delete();
			$this->redirect('/admin/slider/');
		}

// Ustaw kolejność
		public function ustawAction() {
			$db = Zend_Registry::get('db');
			$updateRecordsArray = $_POST['recordsArray'];
			$listingCounter = 1;
			foreach ($updateRecordsArray as $recordIDValue) {
				$data = array('sort' => $listingCounter);
				$db->update('slider', $data, 'id = '.$recordIDValue);
				$listingCounter = $listingCounter + 1;
				}
		}

// Usun kilka paneli
		public function kilkaAction() {
			$db = Zend_Registry::get('db');
			$checkbox = $_POST[checkbox];
			for($i=0;$i<count($_POST[checkbox]);$i++){
				$id = $checkbox[$i];
				$where = $db->quoteInto('id = ?', $id);
				$slider = $db->fetchRow($db->select()->from('slider')->where('id = ?', $id));
				
				unlink(FILES_PATH."/slider/".$slider->plik);
				unlink(FILES_PATH."/slider/thumbs/".$slider->plik);
							
				$db->delete('slider', $where);
			}
			$this->_redirect('/admin/slider/');
	}
	
// Ustawienia slidera
        public function ustawieniaAction() {
            $db = Zend_Registry::get('db');

            $form = new Form_SliderUstawieniaForm();
            $this->view->form = $form;

            // Polskie tlumaczenie errorów
            $polish = kCMS_Polish::getPolishTranslation();
            $translate = new Zend_Translate('array', $polish, 'pl');
            $form->setTranslator($translate);

            $form->getElement('speed')->getDecorator('label')->setOption('escape', false);
            $form->getElement('timeout')->getDecorator('label')->setOption('escape', false);

            $ustawienia = $db->fetchRow($db->select()->from('ustawienia'));

            $form->auto->setvalue($ustawienia->slider_auto);
            $form->pause->setvalue($ustawienia->slider_pause);
            $form->nav->setvalue($ustawienia->slider_nav);
            $form->pager->setvalue($ustawienia->slider_pager);
            $form->speed->setvalue($ustawienia->slider_speed);
            $form->timeout->setvalue($ustawienia->slider_timeout);
            $form->efekt->setvalue($ustawienia->slider_efekt);

            //Akcja po wcisnieciu Submita
            if ($this->_request->getPost()) {

                //Odczytanie wartosci z inputów $auto, $pause, $nav, $pager, $speed, $timeout
                $auto = $this->_request->getPost('auto');
                $pause = $this->_request->getPost('pause');
                $nav = $this->_request->getPost('nav');
                $pager = $this->_request->getPost('pager');
                $speed = $this->_request->getPost('speed');
                $timeout = $this->_request->getPost('timeout');
                $efekt = $this->_request->getPost('efekt');
                $formData = $this->_request->getPost();

                //Sprawdzenie poprawnosci forma
                if ($form->isValid($formData)) {

                    $data = array(
                    'slider_auto' => $auto,
                    'slider_pause' => $pause,
                    'slider_nav' => $nav,
                    'slider_pager' => $pager,
                    'slider_speed' => $speed,
                    'slider_timeout' => $timeout,
                    'slider_efekt' => $efekt,
                    );

                }

                $db->update('ustawienia', $data);
                $this->_redirect('/admin/slider/ustawienia/');
            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
            }
        }
}