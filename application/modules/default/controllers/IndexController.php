<?php
class Default_IndexController extends kCMS_Site
{
    private $Slider;
    private $Image;

    private $locale;

    public function preDispatch() {
        $this->Slider = new Model_SliderModel();
        $this->Image= new Model_ImageModel();

        if($this->canbetranslate) {
            $this->locale = Zend_Registry::get('Zend_Locale')->getLanguage();
        } else {
            $this->locale = 'pl';
        }
    }

    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();

        if($this->locale == 'pl') {
            $atuty = $this->Image->getImages();
        } else {
            $atuty = $this->Image->getImagesTranslated();
        }

        $array = array(
            'slider' => $this->Slider->fetchAll($this->Slider->select()->order('sort ASC')),
            'atuty' => $atuty
        );
        $this->view->assign($array);
    }
}