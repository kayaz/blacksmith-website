<?php

class Default_ErrorController extends kCMS_Site
{

    public function errorAction()
    {
        $this->_helper->layout->setLayout('page');
    }

    public function denyAction()
    {
        $this->_helper->layout->setLayout('page');
    }
}