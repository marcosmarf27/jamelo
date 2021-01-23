<?php
/**
 * Multi Step 1
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class Desligado extends TPage
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
        
        try
        {
            // create the HTML Renderer

          
            $this->html = new THtmlRenderer('app/resources/desligado.html');
            $this->html->enableSection('main');
            

            
            // wrap the page content using vertical box
            $vbox = new TVBox;
            $vbox->style = 'width: 100%';
           // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
           // $vbox->add( $pagestep );
            $vbox->add( $this->html );
            parent::add($vbox);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    function loadPage()
    {}
}