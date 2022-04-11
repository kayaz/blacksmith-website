<?php
class Model_MenuModel  extends Zend_Db_Table_Abstract
{
    protected $_name = 'strony';
    protected $_module = 'menu';
    protected $_db_table;
    protected $_locale;
    private $canbetranslate;

    public function __construct()
    {
        try {
            $this->_db_table = Zend_Registry::get('db');
            $this->_db_table->setFetchMode(Zend_Db::FETCH_OBJ);
        } catch (Zend_Exception $e) {
        }
        $this->_locale = 'pl';
    }

    /**
     * Pokaz wybrana strone
     * @param int $id
     * @return Object
     */
    public function getPageById(int $id)
    {
        if($this->_locale == 'pl') {
            $pageQuery = $this->_db_table->select()
                ->from($this->_name,
                    array (
                        'id',
                        'nazwa',
                        'tekst',
                        'tag',
                        'meta_slowa',
                        'meta_opis',
                        'meta_tytul',
						'plik'
                    ))
                ->where('id = ?', $id);
            return $this->_db_table->fetchRow($pageQuery);
        } else {
            $pageQuery = $this->_db_table->select()
				->from(array('t' => 'tlumaczenie_wpisy'))
				->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
					'plik'
				))
                ->where('module = ?', $this->_module)
                ->where('id_wpis = ?', $id)
                ->where('lang = ?', $this->_locale);
            return $this->_db_table->fetchRow($pageQuery);
        }
    }

    /**
     * Pokaz wybrana strone
     * @param int $id
     * @return Object
     */
    public function getPageByUri(string $uri)
    {
        if($this->_locale == 'pl') {
            $pageQuery = $this->_db_table->select()
                ->from($this->_name,
                    array (
                        'id',
                        'uri',
                        'nazwa',
                        'tekst',
                        'tag',
                        'meta_slowa',
                        'meta_opis',
                        'meta_tytul',
						'plik'
                    ))
                ->where('uri = ?', $uri);
            return $this->_db_table->fetchRow($pageQuery);
        } else {
            $pageQuery = $this->_db_table->select()
                ->from($this->_name,
                    array (
                        'id',
                        'uri'
                    ))
                ->where('uri = ?', $uri);
            $page = $this->_db_table->fetchRow($pageQuery);

            $pageTranslateQuery = $this->_db_table->select()
				->from(array('t' => 'tlumaczenie_wpisy'))
				->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
					'plik'
				))
                ->where('module = ?', $this->_module)
                ->where('id_wpis = ?', $page->id)
                ->where('lang = ?', $this->_locale);
            return $this->_db_table->fetchRow($pageTranslateQuery);
        }
    }
}