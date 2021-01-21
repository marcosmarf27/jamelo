<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Validator\TRequiredValidator;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Wrapper\BootstrapDatagridWrapper;

/**
 * StandardFormDataGridView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EnderecoFormCliente extends TPage
{
    protected $form;      // form
    protected $datagrid;  // datagrid
    protected $loaded;
    protected $pageNavigation;  // pagination component
    
    // trait with onSave, onEdit, onDelete, onReload, onSearch...
    use Adianti\Base\AdiantiStandardFormListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('jamelo'); // define the database
        $this->setActiveRecord('Endereco'); // define the Active Record
        $this->setDefaultOrder('id', 'asc'); // define the default order
        $this->setLimit(-1); // turn off limit for datagrid
        $criteria = new TCriteria();
        $criteria->add(new TFilter('system_user_id','=', TSession::getValue('userid')));
        $this->setCriteria($criteria);

        
        // create the form
        $this->form = new BootstrapFormBuilder('form_categories');
        $this->form->setFormTitle('Endereço para entrega');
        
        // create the form fields
        $id     = new THidden('id');
        $tipo   = new TRadioGroup('tipo');
        $items = ['1'=>'Principal', '2'=>'Secundário'];
     
        $tipo->addItems($items);
       // $tipo->setUseButton();
        $rua   = new TEntry('rua');
        $bairro   = new TEntry('bairro');
        $numero   = new TEntry('numero');
        $complemento   = new TEntry('complemento');
        $cidade   = new TDBCombo('cidade_id', 'jamelo', 'Cidade', 'id', 'nome');
        $cidade->enableSearch();
        $lat   = new THidden('lat');
        $lon   = new THidden('lon');
        $estado_id   = new THidden('estado_id');
        $obs   = new TText('obs');

       
      
        
        // add the form fields
      
        $this->form->addFields( [new TLabel('Rua')],  [$rua] );
        $this->form->addFields( [new TLabel('Bairro')],  [$bairro], [new TLabel('Número')],  [$numero] );
      
        $this->form->addFields( [new TLabel('Complemento')],  [$complemento] );
        $this->form->addFields( [new TLabel('Cidade')],  [$cidade] );
        $this->form->addFields([new TLabel('Observações/Ponto de referência')], [$obs] );
     
        $this->form->addFields([$lat] );
        $this->form->addFields([$lon] );
        $this->form->addFields([$estado_id] );
       
        $this->form->addFields([$id] );
        
        $rua->addValidation('Rua', new TRequiredValidator);

        $tipo->setLayout('horizontal');
      
        
        // define the form actions
        $this->form->addAction( 'Salvar', new TAction([$this, 'onSave']), 'fas:save green');
       // $this->form->addActionLink( 'Clear',new TAction([$this, 'onClear']), 'fa:eraser red');
        
        // make id not editable
        $id->setEditable(FALSE);
        
        // create the datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
        //$col_id    = new TDataGridColumn('id', 'Id', 'right', '10%');
        $col_name  = new TDataGridColumn('rua', 'Rua', 'left', '30%');
        $numero  = new TDataGridColumn('numero', 'Nº', 'left', '15%');
        $bairro  = new TDataGridColumn('bairro', 'Bairro', 'left', '30%');
        $obs  = new TDataGridColumn('obs', 'Obs', 'left', '25%');
        
        //$this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_name);
        $this->datagrid->addColumn($numero);
        $this->datagrid->addColumn($bairro);
        $this->datagrid->addColumn($obs);
 
        
       // $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_name->setAction( new TAction([$this, 'onReload']), ['order' => 'rua']);
        
        // define row actions
        $action1 = new TDataGridAction([$this, 'onEdit'],   ['key' => '{id}'] );
        $action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}'] );
        
        $this->datagrid->addAction($action1, 'Edit',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Delete', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // wrap objects inside a table
      /*   $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid));
        
        // pack the table inside the page
        parent::add($vbox); */

        $pagestep = new TPageStep;
        $pagestep->addItem('Cadastro');
        $pagestep->addItem('Informações básicas');
        $pagestep->addItem('Endereço');
        $pagestep->addItem('Pagamento');
        $pagestep->select('Endereço');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
       
       
        $vbox->add( $this->datagrid );
        $vbox->add( $this->form );
        parent::add($vbox);
    }

     public function onSave($param){
                 TTransaction::open('jamelo');
          
            
           // run form validation
            $login = TSession::getValue('passologin');
            $data = $this->form->getData();
            $data->system_user_id = TSession::getValue('userid');
          
          
            $object = new Endereco;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // fill the form with the active record data
            $this->form->setData($object);
          
            
            TTransaction::close();  
            TToast::show('success', 'Endereço incluido com sucesso!', 'bottom center', 'far:check-circle' );
            $this->onReload($param);
            
           
    } 
}
