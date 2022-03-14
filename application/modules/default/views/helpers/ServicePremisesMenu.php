<?php

class Zend_View_Helper_ServicePremisesMenu extends Zend_View_Helper_Abstract {

    function servicePremisesMenu()
    {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);

        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $configUrl = $config->getOption('resources');
        $baseUrl = $configUrl['frontController']['baseUrl'];

        $query = $db->select()
            ->from('miasta', array('id', 'nazwa'))
            ->join('inwestycje', 'miasta.id = inwestycje.miasto', array('nazwa as inwestycja_nazwa', 'slug as inwestycja_slug', 'status', 'miasto', 'uslugowe'))
            ->where('inwestycje.uslugowe =?', 1);
        $sql = $db->fetchAll($query);

        if(count($sql) > 0) {
            $html = '<ul class="submenu list-unstyled mb-0">';
            foreach ($sql as $s) {
                $html .= '<li><a href="' . $baseUrl . '/pl/' . slug($s['nazwa']) . '/uslugowe/i/' . $s['inwestycja_slug'] . '/"><b>' . $s['nazwa'] . '</b>: ' . $s['inwestycja_nazwa'] . '</a></li>';
            }
            $html .= '</ul>';

            return $html;
        }
    }
}