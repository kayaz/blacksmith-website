<?php
class Form_NazwaPlikTekstForm extends Zend_Form
{
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('nazwaplik');
        $this->setAttrib('class', 'mainForm');

        $nazwa = new Zend_Form_Element_Text('nazwa');
        $nazwa->setLabel('Nazwa')
            ->setRequired(true)
            ->setAttrib('size', 83)
            ->setAttrib('class', 'validate[required]')
            ->addValidator('NotEmpty')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $galeria = new Zend_Form_Element_Select('galeria');
        $galeria->setLabel('Galeria')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
        $db = Zend_Registry::get('db');

        $katalog = $db->fetchAll($db->select()->from('galeria')->order( 'nazwa ASC' ));
        foreach ($katalog as $listItem) {
            $galeria->addMultiOption($listItem->id, $listItem->nazwa);
        }

        $tekst = new Zend_Form_Element_Textarea('tekst');
        $tekst->setLabel('Tekst')
            ->setRequired(true)
            ->setAttrib('rows', 19)
            ->setAttrib('cols', 100)
            ->setAttrib('class', 'editor')
            ->addValidator('NotEmpty')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fullformRowtext')),
                array('Label'), array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fullformRow'))));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel ('Zapisz')
            ->setAttrib('class', 'greyishBtn')
            ->setDecorators(array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formSubmit'))));

        // Polskie tlumaczenie errorow
        $polish = kCMS_Polish::getPolishTranslation();
        $translate = new Zend_Translate('array', $polish, 'pl');
        $this->setTranslator($translate);

        $this->setDecorators(array('FormElements',array('HtmlTag'),'Form',));
        $this->addElements(array($nazwa, $galeria, $tekst, $submit));
    }
}