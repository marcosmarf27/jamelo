<?php
/**
 * SystemDocumentUser
 *
 * @version    1.0
 * @package    model
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class Pedido extends TRecord
{
    const TABLENAME = 'pedido';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
   // const CREATEDAT = 'criacao';
    //const UPDATEDAT = 'atualizacao';
    private $cliente;
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('data_pedido');
        parent::addAttribute('mes');
        parent::addAttribute('ano');
        parent::addAttribute('system_user_id');
        parent::addAttribute('obs');
        parent::addAttribute('pagamento');
        parent::addAttribute('troco');
        parent::addAttribute('fase');
        parent::addAttribute('status');
        parent::addAttribute('data_previsao');
        parent::addAttribute('data_entrega');
        parent::addAttribute('data_pag');
        parent::addAttribute('valor_pag');
        parent::addAttribute('total');//valor do pedido
        parent::addAttribute('pontovalor');// valor descontado
        parent::addAttribute('valorcomdesc');// valor do pedido com desconto
    }

    function get_cliente()
    {
        // instantiates City, load $this->city_id
        if (empty($this->cliente))
        {
            $this->cliente = new SystemUser($this->system_user_id);
        }
        
        // returns the City Active Record
        return $this->cliente;
    }
}