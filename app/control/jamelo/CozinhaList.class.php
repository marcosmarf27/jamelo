<?php
/**
 * StandardDataGridView Listing
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CozinhaList extends TPage
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
        parent::setTargetContainer("adianti_right_panel");
        
        
        $this->setDatabase('jamelo');        // defines the database
        $this->setActiveRecord('PedidoItem');       // defines the active record
        $this->setDefaultOrder('pedido_id', 'asc');
       
        $criteria1 = new TCriteria;
        $criteria2 = new TCriteria;
        $criteria1->add(new TFilter('pedido_id', 'IN', '(SELECT id FROM pedido where fase = 2)'));
        $criteria2->add(new TFilter('produto_categoria_id', 'IN', array(1)));

        $criteria = new TCriteria;     
        $criteria->add($criteria1); 
        $criteria->add($criteria2);
       
       
      
        $this->setCriteria($criteria1);
        
  
       
        
        // creates the form
        
        
     
        
     
        
 
        
        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";
        
        // creates the datagrid columns
        $col_id    = new TDataGridColumn('pedido_id', 'Pedido', 'right', '10%');
        $col_name  = new TDataGridColumn('item->nome', 'Item', 'left', '50%');
        $col_qtd  = new TDataGridColumn('qtd', 'Quantos?', 'left', '20%');
        $col_state = new TDataGridColumn('pedido->data_pedido', 'Horário', 'center', '20%');
    
        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_name);
        $this->datagrid->addColumn($col_qtd);
        $this->datagrid->addColumn($col_state);
        
       // $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'pedido_id']);
       $col_qtd->setTransformer( function($value, $object, $row) {
        $div = new TElement('span');
        $div->class="label label-info";
        $div->style="text-shadow:none; font-size:18px";
        $div->add($value);
        return $div;
    });

    $col_name->setTransformer(array($this, 'exibicaoCozinha'));
    $col_id->setDataProperty('style','font-weight: bold');
    $col_state->setDataProperty('style','font-weight: bold');
        
    $col_state->setTransformer( function($value, $object, $row) {
        $date = new DateTime($value);
        return $date->format('H:i:s');
    });
       
        
        // create the datagrid model
        $this->datagrid->createModel();

        $back = new TActionLink('Fechar', new TAction(array($this, 'onClose')), 'black', null, null, 'fas:window-close red');
        $back->addStyleClass('btn btn-default btn-sm');
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        
        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack($back, $this->datagrid, $this->pageNavigation));
        
        // add the table inside the page
        parent::add($vbox);
    }
    
    /**
     * Clear filters
     */
    function clear()
    {
        $this->clearFilters();
        $this->onReload();
    }

    public function exibicaoCozinha($value, $object, $row)
    {
       
       
       
        
        
      
 
           
          
            return "<span id = 'cozinhaitem'  style='color:white; font-size: 25px; background: {$object->item->cor}'>$value</span>";
        
    }
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
    
}