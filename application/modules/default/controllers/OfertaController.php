<?php

class Default_OfertaController extends kCMS_Site
{
    private $page_id;
    private $locale;
    private $Model;

    public function preDispatch() {
        $this->page_id = 4;
        $this->Model = new Model_OfferModel();

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

            if($this->locale == 'pl') {
                $katalog = $this->Model->get();
            } else {
                $katalog_pl = $this->Model->get();
                $katalog = $this->Model->getTranslated('offer', $katalog_pl->id, $this->locale);
            }

            $array = array(
                'pageclass' => ' about-page',
                'strona_id' => $this->page_id,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => (isset($page->meta_tytul)) ? $page->meta_tytul : json_decode($page->json)->meta_tytul,
                'seo_opis' => (isset($page->meta_opis)) ? $page->meta_opis : json_decode($page->json)->meta_opis,
                'seo_slowa' => (isset($page->meta_slowa)) ? $page->meta_slowa : json_decode($page->json)->meta_slowa,
                'breadcrumbs' => $breadcrumbs,
                'page' => $page,
                'katalog' => $katalog
            );
            $this->view->assign($array);
        }
    }
}