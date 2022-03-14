<?php

class Default_GalleryController extends kCMS_Site
{

    private int $page_id;
    private $locale;

    public function preDispatch() {
        $this->page_id = 2;
        if($this->canbetranslate) {
            $this->locale = Zend_Registry::get('Zend_Locale')->getLanguage();
        } else {
            $this->locale = 'pl';
        }
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

            $db = Zend_Registry::get('db');

            $katalogQuery = $db->select()
                ->from('galeria')
                ->order('sort ASC');
            $katalog = $db->fetchAll($katalogQuery);

            $array = array(
                'pageclass' => ' gallery-page',
                'strona_id' => $this->page_id,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => (isset($page->meta_tytul)) ? $page->meta_tytul : json_decode($page->json)->meta_tytul,
                'seo_opis' => (isset($page->meta_opis)) ? $page->meta_opis : json_decode($page->json)->meta_opis,
                'seo_slowa' => (isset($page->meta_slowa)) ? $page->meta_slowa : json_decode($page->json)->meta_slowa,
                'content' => (isset($page->tekst)) ? $page->tekst : json_decode($page->json)->tekst,
                'breadcrumbs' => $breadcrumbs,
                'katalog' => $katalog,
                'page' => $page
            );
            $this->view->assign($array);
        }
    }


    public function showAction() {
        $this->_helper->layout->setLayout('page');

        $pageModel = new Model_MenuModel();
        $page = $pageModel->getPageById($this->page_id);

        if(!$page) {
            errorPage();
        } else {
            $db = Zend_Registry::get('db');
            $slug = $this->getRequest()->getParam('slug');

            $katalogQuery = $db->select()
                ->from('galeria')
                ->where('slug=?', $slug);
            $katalog = $db->fetchRow($katalogQuery);

            $photoQuery = $db->select()
                ->from('galeria_zdjecia')
                ->where('id_gal = ?', $katalog->id);
            $photos = $db->fetchAll($photoQuery);

            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            $catalogName = (isset($katalog->nazwa)) ? $katalog->nazwa : json_decode($katalog->json)->nazwa;

            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="'.$this->view->url(array('language'=> $this->locale), 'gallery').'"><span itemprop="name">'.$pageName.'</span></a></li><li class="sep"></li>';
            $breadcrumbs .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$catalogName .'</b><meta itemprop="position" content="2" /></li>';

            $array = array(
                'pageclass' => ' gallery-page',
                'strona_id' => $this->page_id,
                'strona_h1' => $catalogName,
                'strona_tytul' => ' - '.$pageName.' - '.$catalogName,
                'seo_tytul' => (isset($katalog->meta_tytul)) ? $katalog->meta_tytul : json_decode($katalog->json)->meta_tytul,
                'seo_opis' => (isset($katalog->meta_opis)) ? $katalog->meta_opis : json_decode($katalog->json)->meta_opis,
                'breadcrumbs' => $breadcrumbs,
                'katalog' => $katalog,
                'photos' => $photos,
                'page' => $page
            );
            $this->view->assign($array);
        }
    }
}