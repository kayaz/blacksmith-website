<?php
class Model_OfferModel  extends Zend_Db_Table_Abstract
{
    public $_name = 'oferta';
    public $_module = 'offer';
    protected $_locale;
    protected $_db_table;

    public function init()
    {
        try {
            $this->_db_table = Zend_Registry::get('db');
            $this->_db_table->setFetchMode(Zend_Db::FETCH_OBJ);
        } catch (Zend_Exception $e) {
        }
    }

    public function getTranslated()
    {
        $translatedQuery = $this->_db_table->select()
            ->from(array('t' => 'tlumaczenie_wpisy'))
            ->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
                'sort',
                'galeria'
            ))
            ->where('module = ?', $this->_module)
            ->where('lang = ?', $this->_locale)
            ->order('sort ASC');
        return $this->_db_table->fetchAll($translatedQuery);
    }

    public function get()
    {
        return $this->fetchAll(
            $this->select()
                ->order('sort ASC')
        );
    }
}