<?php
class Model_SliderModel  extends Zend_Db_Table_Abstract
{
    protected $_name = 'slider';
    protected $_module = 'slider';
    protected $_db_table;
    protected $_locale;
    private $canbetranslate;

    const IMG_WIDTH = 1920;
    const IMG_HEIGHT = 760;

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

    /**
     * Pokaz liste paneli
     */
    public function getAll()
    {
        $sliderAllQuery = $this->_db_table->select()
            ->from(array('n' => $this->_name),
                array(
                    'id',
                    'plik',
                    'tytul',
                    'link',
                    'link_tytul',
                    'sort'
                ))
            ->order('n.sort ASC');
        return $this->_db_table->fetchAll($sliderAllQuery);
    }

    /**
     * Pokaz liste paneli przetlumaczonych
     */
    public function getAllTranslated()
    {
        $newsTranslatedQuery = $this->_db_table->select()
            ->from(array('t' => 'tlumaczenie_wpisy'))
            ->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
                'sort',
                'plik'
            ))
            ->where('module = ?', $this->_module)
            ->where('lang = ?', $this->_locale);
        return $this->_db_table->fetchAll($newsTranslatedQuery);
    }
}