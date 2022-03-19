<?php
class Model_ImageModel  extends Zend_Db_Table_Abstract
{
    public $_name = 'image';
    public $_module = 'images';
    protected $_locale;
    protected $_db_table;
    private $canbetranslate;

    public function init()
    {
        try {
            $this->_db_table = Zend_Registry::get('db');
            $this->_db_table->setFetchMode(Zend_Db::FETCH_OBJ);
        } catch (Zend_Exception $e) {
        }
        try {
            $this->canbetranslate = Zend_Registry::get('canbetranslate');
            if($this->canbetranslate) {
                $this->_locale = Zend_Registry::get('Zend_Locale')->getLanguage();
            } else {
                $this->_locale = 'pl';
            }
        } catch (Zend_Exception $e) {
        }
    }

    public function getImagesTranslated()
    {
        $translatedQuery = $this->_db_table->select()
            ->from(array('t' => 'tlumaczenie_wpisy'))
            ->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
                'plik'
            ))
            ->where('module = ?', $this->_module)
            ->where('lang = ?', $this->_locale)
            ->order('sort ASC');
        return $this->_db_table->fetchAll($translatedQuery);
    }

    public function getImages()
    {
        $images = $this->fetchAll(
            $this->select()
                ->order('sort ASC')
        );
        return $images;
    }
}