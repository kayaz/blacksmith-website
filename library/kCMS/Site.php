<?php
require_once 'Zend/Controller/Action.php';
abstract class kCMS_Site extends Zend_Controller_Action {

    protected $canbetranslate;

    public function init() {
        try {
            $db = Zend_Registry::get('db');
        } catch (Zend_Exception $e) {

        }

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $configApp = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'translated');
        if($configApp->app->translate) {
            $locale = Zend_Registry::get('Zend_Locale')->getLanguage();
            $this->canbetranslate = 1;
            Zend_Registry::set('canbetranslate', 1);
        } else {
            $locale = '';
            $this->canbetranslate = 0;
            Zend_Registry::set('canbetranslate', 0);
        }

        $header = $db->fetchRow($db->select()->from('ustawienia'));
        $langs = $db->fetchAll($db->select()->from('tlumaczenie')->order( 'id ASC' )->where('status =?', 1));

        foreach($langs as $l) {
            if($l->kod == $locale) {
                $this->view->main_meta_tytul = $l->meta_tytul;
                $this->view->main_meta_slowa = $l->meta_slowa;
                $this->view->main_meta_opis = $l->meta_opis;
            }
        }

        $sitearray = array(
            'header' => $header,
            'langs' => $langs,
            'lang' => $locale,
            'menu' => new kCMS_MenuBuilder(),
            'canbetranslate' => $this->canbetranslate,
            'current_action' => $request->getActionName(),
            'current_controller' => $request->getControllerName(),
            'user' => Zend_Auth::getInstance()->getIdentity()
        );
        $this->view->assign($sitearray);

        //******** Parse html ********//
        function gallery($input) {
            $db = Zend_Registry::get('db');
            $images = $db->fetchAll($db->select()->from('galeria_zdjecia')->order( 'sort ASC' )->where('id_gal =?', $input[2]));
            $front = Zend_Controller_Front::getInstance();
            $baseUrl = $front->getRequest()->getBaseUrl();
            $action = $front->getRequest()->getActionName();

            if($input[1] == 'galeria') {
                $html = '<div class="row justify-content-center gallery-thumbs">';
                foreach ($images as $value) {
                    $html.= '<div class="col-6 col-sm-4 col-lg-3 col-3-gallery"><div class="col-gallery-thumb"><a href="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" class="swipebox" rel="gallery-1'.$input[2].'" title=""><img src="'.$baseUrl.'/files/galeria/thumbs/'.$value->plik.'"><div></div></a></div></div>';
                }
                $html.= '</div>';
            }
            if($input[1] == 'slider') {
                $html= '<div class="row"><div class="col-12"><div class="sliderWrapper"><ul class="list-unstyled mb-0 clearfix">';
                foreach ($images as $value) {
                    $html.= '<li><a href="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" title="" class="swipebox" rel="gallery-2'.$input[2].'"><img src="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" alt="" /></a></li>';
                }
                $html.= '</ul></div></div></div>';
            }
            if($input[1] == 'karuzela') {
                $html= '<div class="carouselWrapper"><ul class="list-unstyled mb-0 clearfix" data-slick=\'{"slidesToShow": 4}\'>';
                foreach ($images as $value) {
                    $html.= '<li><a href="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" title="" class="swipebox" rel="gallery-3'.$input[2].'"><img src="'.$baseUrl.'/files/galeria/thumbs/'.$value->plik.'" alt="" /></a></li>';
                }
                $html.= '</ul></div>';
            }
            return($html);
        }

        function parse($input) {
            $input = preg_replace_callback('/\[galeria=(.*)](.*)\[\/galeria\]/', 'gallery', $input);
            $input = str_replace("</div></p>","</div>",$input);
            $input = str_replace("<p><div","<div",$input);
            return $input;
        }
        //******** Parse html ********//

        //******** 404 redirect ********//
        function errorPage()
        {
            $front = Zend_Controller_Front::getInstance()->getRequest();
            $response = Zend_Controller_Front::getInstance()->getResponse();

            $layout = Zend_Layout::getMvcInstance();
            $view = $layout->getView();
            $array = array(
                'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                'strona_nazwa' => "Błąd 404",
                'nofollow' => 1,
            );
            $view ->assign($array);

            $front->setModuleName('default')->setControllerName('error')->setActionName('error');
            $response->setHttpResponseCode(404)->setRawHeader('HTTP/1.1 404 Not Found');
        }
        //******** 404 redirect ********//

        //******** dd ********//
        function dd($code)
        {
            $code = Zend_Debug::dump($code, $label = null, $echo = false);
            $code = html_entity_decode($code);
            $str = str_replace(array('&lt;', '&gt;'), array('<', '>'), $code);
            $str = str_replace(array('<?', '?>', '<%', '%>', '\\', '</script>'), array('phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose'), $str);
            $str = '<?php ' . $str . ' ?>';
            $str = highlight_string($str, TRUE);
            if (abs(PHP_VERSION) < 5) {
                $str = str_replace(array('<font ', '</font>'), array('<span ', '</span>'), $str);
                $str = preg_replace('#color="(.*?)"#', 'style="color: \\1"', $str);
            }
            $str = preg_replace('/<span style="color: #([A-Z0-9]+)">&lt;\\?php(&nbsp;| )/i', '<span style="color: #$1">', $str);
            $str = preg_replace('/(<span style="color: #[A-Z0-9]+">.*?)\\?&gt;<\\/span>\\n<\\/span>\\n<\\/code>/is', "\$1</span>\n</span>\n</code>", $str);
            $str = preg_replace('/<span style="color: #[A-Z0-9]+"\\><\\/span>/i', '', $str);
            $str = str_replace(array('phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose'), array('&lt;?', '?&gt;', '&lt;%', '%&gt;', '\\', '&lt;/script&gt;'), $str);
            echo $str;
            exit;
        }
        //******** dd ********//

        //******** cut the words ********//
        function previewParser($string, $len) {
            $pattern_clear = array(
                '@(\[)(.*?)(\])@si',
                '@(\[/)(.*?)(\])@si'
            );

            $replace_clear = array(
                '',
                ''
            );

            $string = preg_replace($pattern_clear, $replace_clear, $string);
            if (strlen($string) > $len) {
                $result = mb_substr($string, 0, $len, "UTF-8") . ' ...';
            } else {
                $result = $string;
            }
            return $result;
        }
        //******** cut the words ********//

        //******** slug ********//
        function slug($value) {
            $value = strtr($value, array('ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'));
            $value = str_replace(' ', '-', trim($value));
            $value = preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $value);
            $value = preg_replace('/[\-]+/', '-', $value);
            $value = stripslashes($value);
            return urlencode(strtolower($value));
        }
        //******** slug ********//

        //******** image slug ********//
        function slugImg($title, $file) {
            $slug = slug($title);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            return $slug.'.'.$ext;
        }
        //******** image slug ********//
	}
}
?>