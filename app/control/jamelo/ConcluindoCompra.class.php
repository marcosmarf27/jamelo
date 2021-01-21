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
class ConcluindoCompra extends TPage
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

            if(TSession::getValue('logged'))
            {
                AdiantiCoreApplication::loadPage('ResumoLogado', 'onReload');
            }
            $this->html = new THtmlRenderer('app/resources/tutor/welcome.html');
            $this->html->enableSection('main');
            
            $pagestep = new TPageStep;
            $pagestep->addItem('Cadastro');
            $pagestep->addItem('Informações básicas');
            $pagestep->addItem('Endereço');
            $pagestep->addItem('Pagamento');
            $pagestep->select('Cadastro');
            
            // wrap the page content using vertical box
            $vbox = new TVBox;
            $vbox->style = 'width: 100%';
           // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $vbox->add( $pagestep );
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