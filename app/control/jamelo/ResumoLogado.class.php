<?php




use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Util\TActionLink;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TText;
use Adianti\Wrapper\BootstrapDatagridWrapper;

class ResumoLogado extends TPage
{
    private $datagrid;
    
    public function __construct()
    {
        parent::__construct();

        TTransaction::open('jamelo');
        $preferencias = SystemPreference::getAllPreferences();

    

        if($preferencias){

            if($preferencias['desligado'] == 'sim'){
                AdiantiCoreApplication::loadPage('Desligado', 'loadPage');
            }
        }

        TTransaction::close();
        
        //parent::setTargetContainer("adianti_right_panel");
       
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $preco = new TDataGridColumn('sale_price', 'Preço', 'right',   '20%');
        // add the columns
        //$this->datagrid->addColumn( new TDataGridColumn('id',  'ID',  'center', '10%') );
        $this->datagrid->addColumn( new TDataGridColumn('description',  'Descrição',  'left',   '50%') );
        $this->datagrid->addColumn( new TDataGridColumn('amount',  'Qtd',  'right',   '30%') );
        $this->datagrid->addColumn($preco);

        $preco->enableTotal('sum', 'R$', 2, ',', '.');
        
        $action1 = new TDataGridAction([$this, 'onDelete'],   ['id'=>'{id}' ] );
        $this->datagrid->addAction($action1, 'Excluir itens', 'far:trash-alt red');
        
        // creates the datagrid model
        $this->datagrid->createModel();
        
        $back = new TActionLink('Concluir e fazer o pedido', new TAction(array($this, 'concluirPedido')), 'black', null, null, 'fas:money-check-alt');
        $back->addStyleClass('btn btn-default btn-sm');

        
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $preco->setTransformer( $format_value );
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($back);
        
        

        $pagestep = new TPageStep;
        $pagestep->addItem('Cadastro');
        $pagestep->addItem('Informações básicas');
        $pagestep->addItem('Endereço');
        $pagestep->addItem('Pagamento');
        $pagestep->select('Pagamento');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
       // $vbox->add( $pagestep );
        $vbox->add( $panel );
        parent::add($vbox);
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
    
    public static function concluirPedido( $param )
    {
        
        try
        {
            TTransaction::open('jamelo');
            $userid = TSession::getValue('userid');

            
            if($userid){
                $endereco = Endereco::where('system_user_id', '=', $userid)->load();
              
                if(empty($endereco)){
                    $action = new TAction(array('EnderecoFormCliente', 'onReload'));
                    new TMessage('info', 'Não há endereço cadastrado, por favor cadastre um endereço!', $action);
                    exit;
                }
            }
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
        $form = new BootstrapFormBuilder('input_form_resumo');
        
        $troco = new TEntry('troco');
        $jamelo = new TEntry('jamelo');
        $taxa = new TEntry('taxa');
        $taxa->setNumericMask(2, '.', ',', true);
        $taxa->setValue(2);
        $jamelo->setTip('Seus Jamelos acumulados');
        $entrega =  new TRadioGroup('entrega');
        $entrega->addItems(array('1' => 'Receber em casa', '2' => 'Eu vou buscar'));
        $entrega->setUseButton();
        $entrega->setLayout('horizontal');
        $info = new TText('info');
        $info->setValue('Ao usar seus jamelos como forma de pagamento o sistema irá fazer desconto automaticamente!');
        $info->setSize('100%', 60);
       
        
        $formapagamento    = new TDBRadioGroup('pagamento', 'jamelo', 'Pagamento', 'id', 'nome');
        $formapagamento->setLayout('horizontal');
        $formapagamento->setUseButton();
        $obs   = new TText('obs');
        $obs->setSize('100%', 100);
        $obs->placeholder = 'Digite aqui ajustes que deseja fazer no seu pedido Ex. Não colocar cebola....';
        $troco->setNumericMask(2, '.', ',', TRUE);
        //$troco->setTip('Informe o troco para quanto');
        $troco->placeholder = 'Informe o troco...';

        $formapagamento->setChangeAction(new TAction(array(__CLASS__, 'onChangeType')));
        self::onChangeType( ['pagamento' => '1'] );

        $entrega->setChangeAction(new TAction(array(__CLASS__, 'onChangeType2')));
        self::onChangeType( ['entrega' => '1'] );
       $entrega->setValue(1);


      
        
      
        $form->addFields( [new TLabel('<i class="fas fa-credit-card"></i>')], [$formapagamento]);
        $form->addFields( [new TLabel('<i class="fas fa-money-bill"></i>')], [$jamelo]);
        $form->addFields( [new TLabel('<i class="fas fa-info"></i>')], [$info]);
        $form->addFields( [new TLabel('<i class="fas fa-user-edit"></i>')], [$obs]);
        $form->addFields( [new TLabel('<i class="fas fa-exchange-alt"></i>')], [$troco]);
        $form->addFields( [new TLabel('<i class="fas fa-motorcycle"></i>')], [$entrega]);
        $form->addFields( [new TLabel('Taxa R$')], [$taxa]);

        $jamelo->setEditable(false);
        $info->setEditable(false);
      
        
        $form->addAction('Confirmar Pedido', new TAction([__CLASS__, 'gerarPedido']), 'fa:save green');
        
        
        // show the input dialog
        new TInputDialog('Concluindo Pedido', $form);
    }

    public function gerarPedido($param){
       
       try
       {
        TTransaction::open('jamelo');

        $pedidos = TSession::getValue('cart_items');
        $usuario = TSession::getValue('passologin');
        $opcoes = (object) $param;
        ///grava o pedido
        $pedido = new Pedido();
        $pedido->data_pedido = date('Y-m-d H:i:s');
        $pedido->mes = date('m');
        $pedido->ano = date('Y');
        $pedido->system_user_id = TSession::getValue('userid');
        $pedido->pagamento = $opcoes->pagamento;
        $pedido->obs = $opcoes->obs;
        $pedido->status = 1 ;
        $pedido->fase = 1 ;

        
        if($opcoes->entrega == '1'){
            $pedido->entrega = 2.0;
            $pedido->store();
        }else{
            $pedido->entrega = 0;
            $pedido->store();
        }
        $pedido->store();
        //gera os itens
        $itens = array();
        $total = 0;
        if($pedidos){
           
            $this->datagrid->clear();
            foreach ($pedidos as $id => $amount)
            {
                $product = new Produto($id);
                
                $item = new StdClass;
                
                $item->produto_id = $product->id;
                $item->preco = $product->preco;
                $item->produto_categoria_id = $product->categoria_id;
                $item->qtd      = $amount;
                $item->subtotal  = $amount * $product->preco;
                $item->pedido_id = $pedido->id;
                $item->descricao = $product->descricao;
                $total = $total + $item->subtotal;
                $itens[] = $item;
                
                $itempedidos = new PedidoItem();
                $itempedidos->fromArray( (array) $item); 
                $itempedidos->store();
              
                
                
            }

          

        }

        $pedido->total = $total; //valor pedido original
        $pedido->pontovalor = 0; //valor que foi descontado
        $pedido->valorcomdesc = $total; //valor do pedido com desconto
        $pedido->troco = abs($opcoes->troco -  $pedido->valorcomdesc);
        $pedido->store();
        //se não usar jamelos o sistema vai registrar os pontos
      /*   if ($opcoes->pagamento != '4'){

            $fidelidade = new Fidelidade();
            $fidelidade->system_user_id = $pedido->system_user_id;
            $fidelidade->pedido_id = $pedido->id;
            $fidelidade->valorpedido = $pedido->total;
            $fidelidade->pontovalor = $pedido->total * 0.1;
            $fidelidade->store();
           
            $userpontos = new SystemUser(TSession::getValue('userid'));
            $userpontos->pontos +=  $fidelidade->pontovalor ;
            $userpontos->store();
            
        } */

        if($opcoes->pagamento == '4'){

            $usuariototalponto = new SystemUser(TSession::getValue('userid'));

            $valorpedido = $pedido->total;

           if($valorpedido >= $usuariototalponto->pontos)
           {
               $resultado = $valorpedido - $usuariototalponto->pontos;

               $pedido->valorcomdesc = $resultado;
               $pedido->pontovalor = $usuariototalponto->pontos;
               $pedido->store();

               $usuariototalponto->pontos = 0;
               $usuariototalponto->store();
           }
           else
           {

            $pontorestantes = abs($valorpedido - $usuariototalponto->pontos);

            $pedido->valorcomdesc = 0;
            $pedido->pontovalor = $usuariototalponto->pontos - $pontorestantes;
            $pedido->store();

            $usuariototalponto->pontos = $pontorestantes;
            $usuariototalponto->store();



           }

            







        }

        
        
        $this->onReload($param);
       
        TTransaction::close();

       
        $action = new TAction(array('PedidoListCliente', 'onReload'));
        new TMessage('info', 'Seu pedido foi realizado com sucesso!', $action);
       
        }catch (Exception $e){
           new TMessage('error', $e->getMessage());
           TTransaction::rollback();
       }
       
        
       
    }

      






        



    public static function onChangeType($param)
    {
        TTransaction::open('jamelo');
        $usuariototalponto = new SystemUser(TSession::getValue('userid'));
       
        if($usuariototalponto->pontos){
            $usuariototalpontoformatado = number_format($usuariototalponto->pontos, 2, ',', '.');

            $obj = new stdClass;
            $obj->jamelo = $usuariototalpontoformatado;
            TForm::sendData('input_form_resumo', $obj);
         

        }
        if ($param['pagamento'] == '4' or $param['pagamento'] == '5')
        {
           
            TQuickForm::showField('input_form_resumo', 'jamelo');
            TQuickForm::showField('input_form_resumo', 'info');
           
          
        }
        else
        {
           
            TQuickForm::hideField('input_form_resumo', 'jamelo');
            TQuickForm::hideField('input_form_resumo', 'info');
           
            
        }


        TTransaction::close();
    }

    public static function onChangeType2($param)
    {
        if ($param['entrega'] == '1')
        {
           
            TQuickForm::showField('input_form_resumo', 'taxa');
           
          
        }
        else
        {
           
            TQuickForm::hideField('input_form_resumo', 'taxa');
           
            
        }
    }
}
