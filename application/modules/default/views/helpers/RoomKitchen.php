<?php

class Zend_View_Helper_RoomKitchen extends Zend_View_Helper_Abstract {

	function roomKitchen($numer){
		switch ($numer) {
			case '1':
				return 'Kuchnia';
			case '2':
				return 'Aneks';
		}
	}
}