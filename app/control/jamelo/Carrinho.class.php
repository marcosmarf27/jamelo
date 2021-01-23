<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Widget\Util\TActionLink;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Wrapper\BootstrapDatagridWrapper;

class Carrinho extends TPage
{
    private $datagrid;
    
    public function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer("adianti_right_panel");
       
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->class = 'carrinho';
        $preco = new TDataGridColumn('sale_price', 'Preço', 'right',   '30%');
        $qtd = new TDataGridColumn('amount', 'Qtd', 'right',   '30%');
        // add the columns
        //$this->datagrid->addColumn( new TDataGridColumn('id',  'ID',  'center', '10%') );
        $this->datagrid->addColumn( new TDataGridColumn('description',  'Descrição',  'left',   '40%') );
        $this->datagrid->addColumn( $qtd );
        $this->datagrid->addColumn($preco);

        $preco->enableTotal('sum', 'R$', 2, ',', '.');
        
        $action1 = new TDataGridAction([$this, 'onDelete'],   ['id'=>'{id}' ] );
        $this->datagrid->addAction($action1, 'Excluir itens', 'far:trash-alt red');
        
        // creates the datagrid model
        $this->datagrid->createModel();
        
        $back = new TActionLink('Continuar comprando...', new TAction(array($this, 'onClose')), 'black', null, null, 'fa:arrow-right green');
        $back->addStyleClass('btn btn-default btn-sm');
        $fazerpedido= new TActionLink('Concluir e fazer pedido', new TAction(array('ConcluindoCompra', 'LoadPage'), ['register_state' => 'false']), 'black', null, null, 'fas:money-check-alt white');
        $fazerpedido->addStyleClass('btn btn-success btn-sm');
       

        
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $preco->setTransformer( $format_value );
        $qtd->setTransformer( function($value, $object, $row) {
            $div = new TElement('span');
            $div->class="label label-success";
            $div->style="text-shadow:none; font-size:12px";
            $div->add($value);
            return $div;
        });
        
        $panel = new TPanelGroup;
        $panel->add($fazerpedido);
        $panel->add($this->datagrid);
      
        $panel->addFooter($back);
        //$panel->addHeaderActionLink( 'Concluir e fazer pedido', new TAction(['ConcluindoCompra', 'LoadPage'], ['register_state' => 'false']), 'fas:money-check-alt green' );
        
        parent::add($panel);
    }
    
    /**
     * Delete an item from cart items
     */
    public function onDelete( $param )
    {
        $cart_items = TSession::getValue('cart_items');
        unset($cart_items[ $param['key'] ]);
        TSession::setValue('cart_items', $cart_items);
        
        $this->onReload();
    }
    
    /**
     * Reload the cart list
     */
    public function onReload()
    {
        $cart_items = TSession::getValue('cart_items');
        
        try
        {
            if($cart_items){
                TTransaction::open('jamelo');
                $this->datagrid->clear();
                foreach ($cart_items as $id => $amount)
                {
                    $product = new Produto($id);
                    
                    $item = new StdClass;
                    $item->id          = $product->id;
                    $item->description = $product->nome;
                    $item->amount      = $amount;
                    $item->sale_price  = $amount * $product->preco;
                    
                    $this->datagrid->addItem( $item );
                }
                TTransaction::close();

            }
           
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * shows the page
     */
    function show()
    {
        $this->onReload();
        parent::show();
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
