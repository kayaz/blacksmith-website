<?php

class Default_ContactController extends kCMS_Site
{

    private int $page_id;
    private int $validation;

    public function preDispatch() {
        $this->page_id = 1;
        $this->validation= 1;
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');

        $pageModel = new Model_MenuModel();
        $page = $pageModel->getPageById($this->page_id);

        if(!$page) {
            errorPage();
        } else {
            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$pageName .'</b><meta itemprop="position" content="2" /></li>';

            if ($this->_request->isPost()) {
                $sendEmail = new Mails_ContactSend();
                $trySendEmail = $sendEmail->send($this->_request->getPost());
            }

            $array = array(
                'pageclass' => ' kontakt-page',
                'strona_id' => $this->page_id,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => (isset($page->meta_tytul)) ? $page->meta_tytul : json_decode($page->json)->meta_tytul,
                'seo_opis' => (isset($page->meta_opis)) ? $page->meta_opis : json_decode($page->json)->meta_opis,
                'seo_slowa' => (isset($page->meta_slowa)) ? $page->meta_slowa : json_decode($page->json)->meta_slowa,
                'content' => (isset($page->tekst)) ? $page->tekst : json_decode($page->json)->tekst,
                'validation' => $this->validation,
                'breadcrumbs' => $breadcrumbs,
                'page' => $page,
                'message' => $trySendEmail,
                'nobottom' => 1
            );
            $this->view->assign($array);
        }
    }
}