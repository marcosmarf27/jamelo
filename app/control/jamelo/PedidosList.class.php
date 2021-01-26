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
use Linfo\Extension\Transmission;

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
class PedidosList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $detail_list;
    protected $endereco;
    
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
        $this->addFilterField('system_user_id', '=', 'system_user_id');
        $criteria = new TCriteria();
        $criteria->add(new TFilter('fase','in', array(1,2,3)), TExpression::OR_OPERATOR);
      
        $this->setCriteria($criteria);

        
        //$this->addFilterField('fase', '=', 'fase'); // filterField, operator, formField
        
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
        $this->form->addExpandButton();
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->disableDefaultClick();
        //$this->datagrid->datatable = 'true';
        
        // creates the datagrid columns
        $column_id       = new TDataGridColumn('id', 'Nº', 'center', '5%');
        //$column_date     = new TDataGridColumn('data_pedido', 'Horário', 'center', '10%');
        $column_customer = new TDataGridColumn('cliente->name', 'Cliente', 'left', '10%');
        $column_telefone = new TDataGridColumn('cliente->telefone', 'whatsapp', 'left', '10%');
        $column_fase    = new TDataGridColumn('fase', 'Fase', 'right', '25%');
      //  $column_valorpedido = new TDataGridColumn('total', 'Valor', 'left', '10%');
        $column_valorpedido = new TDataGridColumn('valorcomdesc', 'Valor', 'left', '10%');
       
        $column_valorr = new TDataGridColumn('total', 'Valor', 'left', '10%');
        $column_desconto = new TDataGridColumn('pontovalor', 'Desconto', 'left', '10%');
        $column_valordescontado = new TDataGridColumn('valorcomdesc', 'Valor final', 'left', '10%');
        $column_entrega = new TDataGridColumn('entrega', 'Tx . entrega', 'left', '10%');
        $column_troco = new TDataGridColumn( '={troco} - {entrega} + {pontovalor}', 'Troco', 'left', '10%');
        $column_subtotal = new TDataGridColumn( '={valorcomdesc} + {entrega}', 'Total', 'left', '10%');
      
        $column_obspedido = new TDataGridColumn('obs', 'Ajustes', 'left', '10%');
     
        $column_status    = new TDataGridColumn('pagamento', 'Meio', 'right', '5%');
      
        $column_customer->setDataProperty('style','font-weight: bold');
       // $column_obspedido->setDataProperty('style','font-weight: bold');
        $column_subtotal->setDataProperty('style','font-weight: bold');
      
        
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
       
       // $this->datagrid->addColumn($column_status);
       // $this->datagrid->addColumn($column_valorr);
        $this->datagrid->addColumn($column_customer);
        $this->datagrid->addColumn($column_telefone);
      
        $this->datagrid->addColumn($column_fase);
      
       // $this->datagrid->addColumn($column_valorr);

       $this->datagrid->addColumn($column_valorr);
       $this->datagrid->addColumn($column_desconto);
       $this->datagrid->addColumn($column_valordescontado);
       $this->datagrid->addColumn($column_entrega);
    
        $this->datagrid->addColumn($column_subtotal);
        //$this->datagrid->addColumn($column_localidade);

        
        $this->datagrid->addColumn($column_troco);
        $this->datagrid->addColumn($column_status);

        $this->datagrid->addColumn($column_obspedido);
    
       
      

       /*  $column_status->setTransformer(function($value) {
            $result = Status::findInTransaction('jamelo', $value)->nome;
            $div = new TElement('span');
            $div->class="label label-warning";
            $div->style="text-shadow:none; font-size:12px";
            $div->add($result);
            return $div;
        }); */



        $column_fase->setTransformer(function($value) {
            
               
                
                switch($value)
                {

                    case 1:
                        $icon  = "<i class='fas fa-retweet' aria-hidden='true'></i>";
                      
                        $result =  Fase::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-primary";
                        $div->style="text-shadow:none; font-size:12px";
                        $div->add($result);
                        return "{$icon} $div";
                        break;
                    case 2:
                        $icon  = "<i class='fas fa-user-check' aria-hidden='true'></i>";
                        $result =  Fase::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-success";
                        $div->style="text-shadow:none; font-size:12px";
                        $div->add($result);
                        return "{$icon} $div";
                        break;
                    case 3:
                        $icon  = "<i class='fas fa-motorcycle' aria-hidden='true'></i>";
                       
                        $result =  Fase::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-info";
                        $div->style="text-shadow:none; font-size:12px";
                        $div->add($result);
                        return "{$icon} $div";
                        break;
                        case 4:
                            $icon  = "<i class='fas fa-check-circle' aria-hidden='true'></i>";
                            
                            
                            $result =  Fase::findInTransaction('jamelo', $value)->nome;
                            $div = new TElement('span');
                            $div->class="label label-danger";
                            $div->style="text-shadow:none; font-size:10px";
                            $div->add($result);
                            return "{$icon} $div";
                            break;

                            case 5:
                                $icon  = "<i class='fas fa-window-close' aria-hidden='true'></i>";
    
                                
                                
                                $result =  Fase::findInTransaction('jamelo', $value)->nome;
                                $div = new TElement('span');
                                $div->class="label label-danger";
                                $div->style="text-shadow:none; font-size:10px";
                                $div->add($result);
                                return "{$icon} $div";
                                break;
                    
                    


                }
             
             });

             $column_status->setTransformer(function($value) {
            
               
                
                switch($value)
                {

                    case 1:
                        $icon  = "<i class='fas fa-money-bill-wave' aria-hidden='true'></i>";
                      
                        $result =  Pagamento::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                       
                        $div->class="label label-info";
                        $div->style="text-shadow:none; font-size:12px";
                        $div->add($result);
                        return "{$icon}";
                        break;
                    case 2:
                        $icon  = "<i class='fas fa-credit-card' aria-hidden='true'></i>";
                      
                        $result =  Pagamento::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-info";
                        $div->style="text-shadow:none; font-size:12px";
                        $div->add($result);
                        return "{$icon}";
                        break;
                    case 3:
                        $icon  = "<i class='fas fa-tablet-alt' aria-hidden='true'></i>";

                     
                       
                        $result =  Pagamento::findInTransaction('jamelo', $value)->nome;
                        $div = new TElement('span');
                        $div->class="label label-info";
                        $div->style="text-shadow:none; font-size:12px";
                        $div->add($result);
                        return "{$icon}";
                        break;
                        case 4:
                            $icon  = "<i class='fas fa-check-circle' aria-hidden='true'></i>";
                            $icon2  = "<i class='fas fa-money-bill-wave' aria-hidden='true'></i>";
                            
                            
                            $result =  Pagamento::findInTransaction('jamelo', $value)->nome;
                            $div = new TElement('span');
                            $div->class="label label-info";
                            $div->style="text-shadow:none; font-size:10px";
                            $div->add($result);
                            return "{$icon}+{$icon2}";
                            break;

                            case 5:
                                $icon  = "<i class='fas fa-check-circle' aria-hidden='true'></i>";
                                $icon2  = "<i class='fas fa-credit-card' aria-hidden='true'></i>";
    
                                
                                
                                $result =  Pagamento::findInTransaction('jamelo', $value)->nome;
                                $div = new TElement('span');
                                $div->class="label label-info";
                                $div->style="text-shadow:none; font-size:10px";
                                $div->add($result);
                                return "{$icon}+{$icon2}";
                                break;
                    
                    


                }
             
             });

       //$column_valorr->setTransformer($format_value);

       $column_telefone->setTransformer( function ($value) {
        if ($value)
        {
            $value = str_replace([' ','-','(',')'],['','','',''], $value);
            $icon  = "<i class='fab fa-whatsapp' aria-hidden='true'></i>";
            return "{$icon} <a target='newwindow' href='https://api.whatsapp.com/send?phone=55{$value}&text=Olá'> {$value} </a>";
        }
        return $value;
    });
        
        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
       // $column_date->setAction(new TAction([$this, 'onReload']), ['order' => 'data_pedido']);
        
        // define the transformer method over date
       /*  $column_date->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('H:i:s');
        }); */
        $column_troco->setTransformer($format_value);
        $column_valordescontado->setTransformer($format_value);
        $column_entrega->setTransformer($format_value);
        $column_subtotal->setTransformer($format_value);
        $column_desconto->setTransformer($format_value);
        $column_valorpedido->setTransformer($format_value);
        $column_valorr->setTransformer($format_value);
        $column_obspedido->setTransformer( function($value, $object, $row) {
            $div = new TElement('span');
            $div->class="label label-danger";
            $div->style="text-shadow:none; font-size:18px";
            $div->add($value);
            return $div;
        });

        //$action_view   = new TDataGridAction(['SaleSidePanelView', 'onView'],   ['key' => '{id}', 'register_state' => 'false'] );
        //$action_edit   = new TDataGridAction(['PedidoForm', 'onEdit'],   ['key' => '{id}'] );
      //  $action_cozinha   = new TDataGridAction(['CozinhaList', 'onReload'],  ['key' => '{id}']);
        $action_confirm   = new TDataGridAction([$this, 'confirmarPedido'],   ['key' => '{id}'] );
        $action_detailes = new TDataGridAction(array($this, 'onShowDetail'), ['id' => '{id}', 'system_user_id' => '{system_user_id}'] );
      
        $action_entregar   = new TDataGridAction([$this, 'entregarPedido'],   ['key' => '{id}'] );
        $action_concluir   = new TDataGridAction([$this, 'concluirPedido'],   ['key' => '{id}', 'cliente' => '{system_user_id}'] );

        $action_confirm->setDisplayCondition( array($this, 'displayConfirm') );
        $action_entregar->setDisplayCondition( array($this, 'displayEntregar') );
        $action_concluir->setDisplayCondition( array($this, 'displayConcluir') );
        //$action_endereco   = new TDataGridAction(['EnderecoFormWindow', 'loadPage'],   ['system_user_id' => '{system_user_id}'] );
        //$action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        //$this->datagrid->addAction($action_cozinha, 'Ver itens na cozinha...', 'fas:list fa-fw');
        $this->datagrid->addAction($action_detailes, 'Ver Detalhes', 'fas:fa-hamburger');
        $this->datagrid->addAction($action_confirm, 'Confirmar pedido e enviar para cozinha', 'fas:check green fa-fw');
        $this->datagrid->addAction($action_entregar, 'Pedido pronto, enviar para entrega', 'fas:motorcycle fa-fw');
        $this->datagrid->addAction($action_concluir, 'Confirmar entrega e concluir pedido', 'fas:cash-register black  fa-fw');
      
      
      /*   $this->datagrid->addAction($action_edit, 'Edit',   'far:edit blue fa-fw');
        $this->datagrid->addAction($action_delete, 'Delete', 'far:trash-alt red fa-fw'); */

        /* $action_edit->setLabel('Editar pedido');
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
        $this->datagrid->addActionGroup($action_group); */
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
       // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto;';
       // $panel->addHeaderActionLink( 'PDF', new TAction([$this, 'exportAsPDF'], ['register_state' => 'false']), 'far:file-pdf red' );
       // $panel->addHeaderActionLink( 'Ver Cozinha', new TAction(['CozinhaList', 'onReload'], ['register_state' => 'false']), 'fa:table blue' );
        parent::add($this->datagrid);
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

        SystemMessage::where('system_user_to_id', '=', '1')
        ->where('system_user_id', '=', $pedido->system_user_id)
        ->set('checked', 'Y')
        ->update();

        $action = new TAction(array('PedidosList', 'onReload'));
        new TMessage('info', 'Pedido recebido com sucesso!', $action);

        TTransaction::close();


        
    }

    public function concluirPedido($param){

        TTransaction::open('jamelo');

        $pedido = new Pedido($param['key']);
        $pedido->fase = 4;
        $pedido->store();
        if ($pedido->pagamento != 4){
           
            $fidelidade = new Fidelidade();
            $fidelidade->system_user_id = $pedido->system_user_id;
            $fidelidade->pedido_id = $pedido->id;
            $fidelidade->valorpedido = $pedido->total;
            $fidelidade->pontovalor = $pedido->total * 0.1;
            $fidelidade->store();
           
            $userpontos = new SystemUser($param['cliente']);
            $userpontos->pontos +=  $fidelidade->pontovalor ;
            $userpontos->store();
            
            $msg = new SystemMessage();
            $msg->system_user_id = 1; //admnistrador que envia mensagem
            $msg->system_user_to_id = $pedido->system_user_id;
           
            $msg->subject = "Você ganhou <b> {$fidelidade->pontovalor}</b> jamelos";
            $msg->message = '<p>Obrigado,</p><p>Agradecemos a preferência e <b>ficamos felizes </b>!</p><p><br></p><p>Atenciosamente,&nbsp;</p><p>Jamelo</p>';
            $msg->dt_message = date('Y-m-d H:i:s');
            $msg->checked = 'N';
            $msg->store();
        }
       
        

            $msg = new SystemMessage();
            $msg->system_user_id = 1; //admnistrador que envia mensagem
            $msg->system_user_to_id = $pedido->system_user_id;
           
            $msg->subject = "Pedido concluido e entregue";
            $msg->message = '<p>Obrigado,</p><p>Agradecemos a preferência e <b>ficamos felizes </b>!</p><p><br></p><p>Atenciosamente,&nbsp;</p><p>Jamelo</p>';
            $msg->dt_message = date('Y-m-d H:i:s');
            $msg->checked = 'N';
            $msg->store();

        
       

        $action = new TAction(array('PedidosList', 'onReload'));
        new TMessage('info', 'Pedido Concluido com sucesso!', $action);

        TTransaction::close();


        
    }

    public function displayConfirm( $object )
    {
        if ($object->fase == 1)
        {
            return TRUE;
        }
        return FALSE;
    }
    public function displayEntregar( $object )
    {
        if ($object->fase == 2)
        {
            return TRUE;
        }
        return FALSE;
    }

    public function displayConcluir( $object )
    {
        if ($object->fase == 3 )
        {
            return TRUE;
        }
        return FALSE;
    }
    public function exportAsPDF($param)
    {
        try
        {
            // string with HTML contents
            $html = clone $this->datagrid;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/datagrid-export.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Export datagrid as CSV
     */
    public function exportAsCSV($param)
    {
        try
        {
            // get datagrid raw data
            $data = $this->datagrid->getOutputData();
            
            if ($data)
            {
                $file    = 'app/output/datagrid-export.csv';
                $handler = fopen($file, 'w');
                foreach ($data as $row)
                {
                    fputcsv($handler, $row);
                }
                
                fclose($handler);
                parent::openFile($file);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onShowDetail( $param )
    {
        // get row position
        TTransaction::open('jamelo');
        $this->detail_list = new BootstrapDatagridWrapper( new TDataGrid );
        $this->detail_list->style = 'width:100%';
        $this->detail_list->disableDefaultClick();
        
        $product       = new TDataGridColumn('item->nome',  'Item', 'left', '30%');
        $price         = new TDataGridColumn('preco',  'Preço',    'right');
        $amount        = new TDataGridColumn('qtd',  'Qtd',    'left', '30%');
        $subtotal      = new TDataGridColumn('subtotal',  'Subtotal',    'right');
        //$total         = new TDataGridColumn('total',  'Total',    'right');
        
        $this->detail_list->addColumn( $product );
        //$this->detail_list->addColumn( $price );
        $this->detail_list->addColumn( $amount );
       // $this->detail_list->addColumn( $discount );
       // $this->detail_list->addColumn( $subtotal );

        $subtotal->enableTotal('sum', 'R$', 2, ',', '.');

        $amount->setTransformer( function($value, $object, $row) {
            $div = new TElement('span');
            $div->class="label label-info";
            $div->style="text-shadow:none; font-size:15px";
            $div->add($value);
            return $div;
        });

        $product->setTransformer( function($value, $object, $row) {
            return "<span style='color:white; font-size: 40px; background: {$object->item->cor}'>$value</span>";
        });


    
       

        $this->detail_list->createModel();


        $items = PedidoItem::where('pedido_id', '=', $param['key'])->load();
        $this->detail_list->addItems($items);


       
        $this->endereco = new BootstrapDatagridWrapper( new TDataGrid );
        $this->endereco->style = 'width:100%';
        $this->endereco->disableDefaultClick();
        
        $rua       = new TDataGridColumn('rua',  'Rua', 'left');
        $numero         = new TDataGridColumn('numero',  'Nº',    'right');
        $bairro        = new TDataGridColumn('bairro',  'Bairro',    'center');
        $obs      = new TDataGridColumn('obs',  'Ponto de Ref.',    'right');
        $localidade      = new TDataGridColumn('localidade->nome',  'Local',    'right');
        //$total         = new TDataGridColumn('total',  'Total',    'right');
        
        $this->endereco->addColumn( $rua );
        $this->endereco->addColumn( $numero );
        $this->endereco->addColumn( $bairro );
        $this->endereco->addColumn( $localidade );
       // $this->detail_list->addColumn( $discount );
        $this->endereco->addColumn( $obs );

        $this->endereco->createModel();
        
        $items = Endereco::where('system_user_id', '=', $param['system_user_id'])->load();
        $this->endereco->addItems($items);

        $panel = new TPanelGroup();
        $panel->add($this->detail_list);
        $panel->add($this->endereco);
        //$panel->addFooter('footer');
        
        
        $pos = $this->datagrid->getRowIndex('id', $param['key']);
        
        // get row by position
        $current_row = $this->datagrid->getRow($pos);
        $current_row->style = "background-color: #8D8BC8; color:white; text-shadow:none";
        
        // create a new row
        $row = new TTableRow;
        $row->style = "background-color: #992162";
        $row->addCell('');
        $cell = $row->addCell($panel);
        $cell->colspan =14;
        $cell->style='padding:10px;';
        
        // insert the new row
        $this->datagrid->insert($pos +1, $row);

        TTransaction::close();
    }
    
}
