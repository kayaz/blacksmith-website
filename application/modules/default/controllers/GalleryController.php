<?php

class Default_GalleryController extends kCMS_Site
{

    private int $page_id;
    private $locale;
    private $Model;
    private $Translate;

    public function preDispatch() {
        $this->page_id = 2;
        $this->Model = new Model_GalleryModel();
        $this->Translate = new Model_TranslateModel();

        $this->locale = 'pl';
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

            if($this->locale == 'pl') {
                $katalog = $this->Model->get();
            } else {
                $katalog = $this->Model->getTranslated();
            }

            $array = array(
                'pageclass' => ' gallery-page',
                'strona_id' => $this->page_id,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => (isset($page->meta_tytul)) ? $page->meta_tytul : json_decode($page->json)->meta_tytul,
                'seo_opis' => (isset($page->meta_opis)) ? $page->meta_opis : json_decode($page->json)->meta_opis,
                'seo_slowa' => (isset($page->meta_slowa)) ? $page->meta_slowa : json_decode($page->json)->meta_slowa,
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

            if($this->locale == 'pl') {
                $katalog = $this->Model->fetchRow($this->Model->select()->where('slug =?', $slug));
                $id_gallery = $katalog->id;
            } else {
                $katalog_pl = $this->Model->fetchRow($this->Model->select()->where('slug =?', $slug));
                $id_gallery = $katalog_pl->id;

                $katalog = $this->Translate->getTranslate('gallery', $katalog_pl->id, $this->locale);
            }

            $photoQuery = $db->select()
                ->from('galeria_zdjecia')
                ->where('id_gal = ?', $id_gallery);
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