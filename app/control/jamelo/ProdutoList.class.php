<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Wrapper\BootstrapDatagridWrapper;
/**
 * Product List
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ProdutoList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    // trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('jamelo');                // defines the database
        $this->setActiveRecord('Produto');            // defines the active record
        $this->setDefaultOrder('id', 'asc');          // defines the default order
        $this->addFilterField('descricao', 'ilike'); // add a filter field
                // add a filter field
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Product');
        $this->form->setFormTitle('Lista de produtos');
        
        // create the form fields
        $description = new TEntry('descricao');
       
       
        
        // add a row for the filter field
      
        $this->form->addFields( [new TLabel('Descrição')], [$description] );
        
        $this->form->setData( TSession::getValue('ProdutoList_filter_data') );
        
        $this->form->addAction( 'Procurar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addActionLink( 'Cadastrar',  new TAction(['ProdutoForm', 'onEdit']), 'fa:plus green');
        
        // expand button
        $this->form->addExpandButton();
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->enablePopover('Image', "<img style='max-height: 300px; width: 150px' src='{imagem}'>");

        // creates the datagrid columns
        $col_id          = new TDataGridColumn('id', 'ID', 'center', '20%');
        $col_description = new TDataGridColumn('descricao', 'Descrição', 'left', '35%');
       
        $col_sale_price  = new TDataGridColumn('preco', 'Preço', 'right', '45%');
      
      
        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_description);
      
        $this->datagrid->addColumn($col_sale_price);
     
       
        
        // creates two datagrid actions
        $action1 = new TDataGridAction(['ProdutoForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Edit', 'far:edit blue');
        $this->datagrid->addAction($action2 ,'Delete', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
       // $container->add(new TXMLBreadCrumb('menu.xml', 'ProdutoList'));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }
}
