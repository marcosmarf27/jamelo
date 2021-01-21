<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TCardView;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\THidden;

/**
 * ProductCatalogView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class OfertaList extends TPage
{
    private $form, $cards, $pageNavigation;
    
    use Adianti\Base\AdiantiStandardCollectionTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('jamelo');
        $this->setActiveRecord('Produto');
        $this->addFilterField('descricao', 'ilike', 'descricao');

     
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Product');
        $this->form->setFormTitle('Faça seu pedido...');
        
        $description = new THidden('descricao');
        $this->form->addFields( [$description] );
        
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addAction('Concluir e fazer pedido', new TAction(['ConcluindoCompra', 'Loadpage']), 'fas:money-check-alt green');

        // keep the form filled with the search data
        $description->setValue( TSession::getValue( 'Product_description' ) );

        $this->form->addExpandButton('Concluir e fazer pedido', 'fas:money-check-alt green');
        
        // creates a DataGrid
        $this->cards = new TCardView;
        $this->cards->setContentHeight(170);
        $this->cards->setUseButton();
		//$this->cards->setTitleAttribute('descricao');
		
		$this->setCollectionObject($this->cards);
		
        $this->cards->setItemTemplate('<div style="float:left;width:50%;padding-right:10px">
                                           <br  ><b id = "nomeproduto">{nome} </b> <br> 
		                                   <br> {descricao} <br>
		                                  
		                                    <br> R${preco}
		                               </div>
		                               <div style="float:right;width:50%">
		                                   <img style="height:100px;float:right;margin:5px" src="{imagem}">
		                               </div> ');
        
		$this->cards->addAction(new TAction([$this, 'onSelect'], ['id' => '{id}']),  'Adicionar', 'fa:plus-circle');
		
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$vbox->add($this->form); // add a row to the form
        $vbox->add($this->cards); // add a row for page navigation
        
        // add the table inside the page
        parent::add($vbox);
    }
    
    /**
     * Select product
     */
    public static function onSelect( $param )
    {
        $cart_items = TSession::getValue('cart_items');
        
        if (isset($cart_items[ $param['id'] ]))
        {
            $cart_items[ $param['id'] ] ++;
        }
        else
        {
            $cart_items[ $param['id'] ] = 1;
        }
        
        ksort($cart_items);
        
        TSession::setValue('cart_items', $cart_items);

     
       
        
        AdiantiCoreApplication::loadPage('Carrinho', 'onReload', ['adianti_target_container' => 'adianti_right_panel', 'register_state' => 'false']);
    }
    public function abrir(){
        
    }
}
