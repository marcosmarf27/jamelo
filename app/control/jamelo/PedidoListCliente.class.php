<?php


use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Wrapper\BootstrapDatagridWrapper;
/**
 * SaleList
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PedidoListCliente extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('jamelo');          // defines the database
        $this->setActiveRecord('Pedido');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('system_user_id', '=', 'system_user_id'); // filterField, operator, formField
        $criteria = new TCriteria();
        $criteria->add(new TFilter('system_user_id','=', TSession::getValue('userid')));
        $this->setCriteria($criteria);
        
    /*     $this->addFilterField('data_pedido', '>=', 'date_from', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        $this->addFilterField('data_pedido', '<=', 'date_to', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction */
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Sale');
        $this->form->setFormTitle('Pedidos recebidos');
        
        // create the form fields
        $id        = new TEntry('id');
       // $date_from = new TDate('date_from');
       // $date_to   = new TDate('date_to');
        
        $customer_id = new TDBUniqueSearch('system_user_id', 'jamelo', 'SystemUser', 'id', 'name');
        $customer_id->setMinLength(1);
        $customer_id->setMask('{name} ({id})');
        
        // add the fields
        $this->form->addFields( [new TLabel('Nº pedido')],          [$id]); 
        //$this->form->addFields( [new TLabel('Data (de)')], [$date_from],
                               // [new TLabel('Data (para)')],   [$date_to] );
        $this->form->addFields( [new TLabel('Cliente')],    [$customer_id] );
        
        $id->setSize('50%');
     /*  //  $date_from->setSize('100%');
        $date_to->setSize('100%');
        $date_from->setMask( 'dd/mm/yyyy' );
        $date_to->setMask( 'dd/mm/yyyy' ); */
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('PedidosList_filter_data') );
        
        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Inserir pedido manual',  new TAction(['PedidoForm', 'onEdit']), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->datatable = 'true';
        
        // creates the datagrid columns
        $column_id       = new TDataGridColumn('id', 'Pedido', 'center', '10%');
        $column_date     = new TDataGridColumn('data_pedido', 'Horário', 'center', '15%');
      
        $column_valorr = new TDataGridColumn('total', 'Valor Original', 'left', '15%');
        $column_desconto = new TDataGridColumn('pontovalor', 'Desconto', 'left', '15%');
        $column_subtotal = new TDataGridColumn('valorcomdesc', 'Valor final', 'left', '15%');
      
        $column_fase    = new TDataGridColumn('fase', 'Fase Atual', 'right', '30%');
        $column_valorr->setDataProperty('style','font-weight:  bold');
        $column_desconto->setDataProperty('style','font-weight:  bold');
        $column_subtotal->setDataProperty('style','font-weight:  bold');
      
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };

      
        
       // $column_total->setTransformer( $format_value );
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
       
        //$this->datagrid->addColumn($column_status);
      
       // $this->datagrid->addColumn($column_customer);
        $this->datagrid->addColumn($column_date);
        //$this->datagrid->addColumn($column_total);
        $this->datagrid->addColumn($column_valorr);
        $this->datagrid->addColumn($column_desconto);
        $this->datagrid->addColumn($column_subtotal);
       
        $this->datagrid->addColumn($column_fase);

      /*   $column_status->setTransformer(function($value) {
            $result = Status::findInTransaction('jamelo', $value)->nome;
            $div = new TElement('span');
            $div->class="label label-warning";
            $div->style="text-shadow:none; font-size:12px";
            $div->add($result);
            return $div;
        });
 */
        $column_fase->setTransformer(function($value) {
            
               
                
                switch($value)
                {

                    case 1:
                        $icon  = "<i class='fas fa-retweet' aria-hidden='true'></i>";
                      
                        $result =  Fase::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-primary";
                        $div->style="text-shadow:none; font-size:10px";
                        $div->add($result);
                        return "{$icon} $div";
                        break;
                    case 2:
                        $icon  = "<i class='fas fa-user-check' aria-hidden='true'></i>";
                        $result =  Fase::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-success";
                        $div->style="text-shadow:none; font-size:10px";
                        $div->add($result);
                        return "{$icon} $div";
                        break;
                    case 3:
                        $icon  = "<i class='fas fa-motorcycle' aria-hidden='true'></i>";
                       
                        $result =  Fase::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-info";
                        $div->style="text-shadow:none; font-size:10px";
                        $div->add($result);
                        return "{$icon} $div";
                        break;
                    case 4:
                        $icon  = "<i class='fas fa-check-circle' aria-hidden='true'></i>";
                        
                        
                        $result =  Fase::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-warning";
                        $div->style="text-shadow:none; font-size:10px";
                        $div->add($result);
                        return "{$icon} $div";
                        break;
                    
                    


                }
             
             });

       $column_valorr->setTransformer($format_value);
       $column_desconto->setTransformer($format_value);
       $column_subtotal->setTransformer($format_value);
        
        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $column_date->setAction(new TAction([$this, 'onReload']), ['order' => 'data_pedido']);
        
        // define the transformer method over date
        $column_date->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y H:i:s');
        });

        //$action_view   = new TDataGridAction(['SaleSidePanelView', 'onView'],   ['key' => '{id}', 'register_state' => 'false'] );
        $action_edit   = new TDataGridAction(['PedidoForm', 'onEdit'],   ['key' => '{id}'] );
        $action_cancelar   = new TDataGridAction([$this, 'cancelarPedido'],   ['key' => '{id}'] );
        $action_confirm   = new TDataGridAction([$this, 'confirmarPedido'],   ['key' => '{id}'] );
        $action_entregar   = new TDataGridAction([$this, 'entregarPedido'],   ['key' => '{id}'] );
        $action_endereco   = new TDataGridAction(['EnderecoFormWindow', 'loadPage'],   ['system_user_id' => '{system_user_id}'] );
        //$action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        
       // $this->datagrid->addAction($action_view, _t('View details'), 'fa:search green fa-fw');
      /*   $this->datagrid->addAction($action_edit, 'Edit',   'far:edit blue fa-fw');
        $this->datagrid->addAction($action_delete, 'Delete', 'far:trash-alt red fa-fw'); */
        $this->datagrid->addAction($action_cancelar, 'Cancelar', 'far:trash-alt red fa-fw');
        $action_edit->setLabel('Editar pedido');
        $action_edit->setImage('far:edit blue fa-fw');

        $action_endereco->setLabel('Endereços');
        $action_endereco->setImage('fas:map red fa-fw');

        $action_entregar->setLabel('Enviar para entrega');
        $action_entregar->setImage('fas:motorcycle fa-fw');

        $action_confirm->setLabel('Confirmar e preparar');
        $action_confirm->setImage('fas:check green fa-fw');
        
        $action_group = new TDataGridActionGroup('Ações ', 'fa:th');
        
        $action_group->addHeader('Cadastro');
        $action_group->addAction($action_edit);
        $action_group->addAction($action_endereco);
       
        $action_group->addSeparator();
        $action_group->addHeader('Pedido');
        $action_group->addAction($action_confirm);
        $action_group->addAction($action_entregar);
        
        // add the actions to the datagrid
        //$this->datagrid->addActionGroup($action_group);
        
        // create the datagrid model
        $this->datagrid->createModel();

        $div = new TElement('div');
        $div->class = "row";
        
        $indicator1 = new THtmlRenderer('app/resources/tutor/info-box.html');
        $indicator2 = new THtmlRenderer('app/resources/tutor/info-box.html');
        TTransaction::open('jamelo');
        $totalponto = new SystemUser(TSession::getValue('userid'));
        if($totalponto->pontos >= 0){
            $totalpontoformatado = number_format($totalponto->pontos, 2, ',', '.');
            $indicator1->enableSection('main', ['title'     => 'Seus Jamelos acumulados',
            'icon'       => 'fas fa-money-bill-wave',
            'background' => 'green',
            'value'      =>'R$' . $totalpontoformatado ] );

        }else{
            $indicator1->enableSection('main', ['title'     => 'Pontos/Valores acumulados',
            'icon'       => 'fas fa-money-bill-wave',
            'background' => 'green',
            'value'      => 0.00 ] );
        }
     
        
   


                                            TTransaction::close();
        $div->add( $i1 = TElement::tag('div', $indicator1) );
        $i1->class = 'col-sm-12';
     
      
        
       
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
       // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
       // $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid));
        $container->add($div);
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }

    public function entregarPedido($param){

        
        TTransaction::open('jamelo');

        $pedido = new Pedido($param['key']);
        $pedido->fase = 3;
        $pedido->store();

        $msg = new SystemMessage();
        $msg->system_user_id = 1; //admnistrador que envia mensagem
        $msg->system_user_to_id = $pedido->system_user_id;
        $msg->subject = 'Pedido saiu para entrega';
        $msg->message = '<p>Prezado Cliente,</p><p>Seu pedido <b>saiu para entrega</b> em breve chegará!</p><p><br></p><p>Atenciosamente,&nbsp;</p><p>Jamelo</p>';
        $msg->dt_message = date('Y-m-d H:i:s');
        $msg->checked = 'N';
        $msg->store();
        $action = new TAction(array('PedidosList', 'onReload'));
        new TMessage('info', 'Pedido enviado para entrega!', $action);

        TTransaction::close();

    }
    public function confirmarPedido($param){

        TTransaction::open('jamelo');

        $pedido = new Pedido($param['key']);
        $pedido->fase = 2;
        $pedido->store();

        $msg = new SystemMessage();
        $msg->system_user_id = 1; //admnistrador que envia mensagem
        $msg->system_user_to_id = $pedido->system_user_id;
        $msg->subject = 'Pedido recebido com sucesso';
        $msg->message = '<p>Prezado Cliente,</p><p>Seu pedido foi recebido com <b>sucesso </b>e já estamos preparando!</p><p><br></p><p>Atenciosamente,&nbsp;</p><p>Jamelo</p>';
        $msg->dt_message = date('Y-m-d H:i:s');
        $msg->checked = 'N';
        $msg->store();

        $action = new TAction(array('PedidosList', 'onReload'));
        new TMessage('info', 'Pedido confirmado com sucesso!', $action);

        TTransaction::close();


        
    }

    public function cancelarPedido($param){

        TTransaction::open('jamelo');

        $pedido = new Pedido($param['key']);

        if ($pedido->fase == '1'){

            $pedido->fase = 5;
            $pedido->store();
    
            $msg = new SystemMessage();
            $msg->system_user_id = 1; //admnistrador que envia mensagem
            $msg->system_user_to_id = $pedido->system_user_id;
            $msg->subject = 'Pedido cancelado com sucesso!';
            $msg->message = '<p>Prezado Cliente,</p><p>Seu pedido foi cancelado com <b>sucesso </b>e já estamos preparando!</p><p><br></p><p>Atenciosamente,&nbsp;</p><p>Jamelo</p>';
            $msg->dt_message = date('Y-m-d H:i:s');
            $msg->checked = 'N';
            $msg->store();
    
            $action = new TAction(array('PedidoListCliente', 'onReload'));
            new TMessage('info', 'Pedido Cancelado com sucesso!', $action);


        }else{

            new TMessage('error', 'Para cancelar pedidos já confirmados entre em contato pelo nosso whatsapp!');

        }
   

        TTransaction::close();


        
    }
}
