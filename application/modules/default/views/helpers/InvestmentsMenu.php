<?php

class Zend_View_Helper_InvestmentsMenu extends Zend_View_Helper_Abstract {

    function investmentsMenu(){
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);

        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $configUrl = $config->getOption('resources');
        $baseUrl = $configUrl['frontController']['baseUrl'];
        $result = array();

        $query = $db->select()
            ->from('miasta', array('id', 'nazwa'))
            ->join('inwestycje', 'miasta.id = inwestycje.miasto', array('nazwa as inwestycja_nazwa', 'slug as inwestycja_slug', 'status', 'miasto'))
            ->where('inwestycje.status =?', 1);
        $sql = $db->fetchAll($query);

        foreach($sql as $val) {
            if(array_key_exists('nazwa', $val)){
                $result[$val['nazwa']][] = $val;
            }else{
                $result[""][] = $val;
            }
        }

        $html = '<ul class="submenu list-unstyled mb-0">';
        foreach($result as $c => $city) {
            $html .= '<li><h3><a href="'.$baseUrl.'/pl/lokalizacja/'.slug($c).'/">' . $c . '</a></h3></li>';
            foreach ($city as $invest){
                $html .='<li>';
                $html .='<a href="'.$baseUrl.'/pl/'.slug($c).'/i/'.$invest['inwestycja_slug'].'/"><span>&bull;</span> '.$invest['inwestycja_nazwa'].'</a></li>';
                $html .='</li>';
            }
        }
        $html .= '</ul>';

        return $html;
    }
}