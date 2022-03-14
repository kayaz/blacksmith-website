<?php
class Zend_View_Helper_MenuPosition extends Zend_View_Helper_Abstract {

    public function menuPosition(int $id) {
        switch ($id) {
            case '1':
                return 'Górne menu';
            case '2':
                return 'Stopka';
            case '3':
                return 'Górne menu i stopka';
            case '0':
                return 'Ukryte';
        }
    }
}