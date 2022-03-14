<?php
class Default_IndexController extends kCMS_Site
{
    private $Slider;

    public function preDispatch() {
        $this->Slider = new Model_SliderModel();
    }

    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();
        $array = array(
            'slider' => $this->Slider->fetchAll($this->Slider->select()->order('sort ASC'))
        );
        $this->view->assign($array);
    }
}