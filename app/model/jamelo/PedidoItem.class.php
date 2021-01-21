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
class PedidoItem extends TRecord
{
    const TABLENAME = 'pedido_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $pedido;
    private $item;
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('produto_id');
        parent::addAttribute('qtd');
        parent::addAttribute('preco');
        parent::addAttribute('subtotal');
        parent::addAttribute('pedido_id');
        parent::addAttribute('produto_categoria_id');
       
    }

    function get_pedido()
    {
        // instantiates City, load $this->city_id
        if (empty($this->pedido))
        {
            $this->pedido = new Pedido($this->pedido_id);
        }
        
        // returns the City Active Record
        return $this->pedido;
    }
    
    function get_item()
    {
        // instantiates City, load $this->city_id
        if (empty($this->item))
        {
            $this->item = new Produto($this->produto_id);
        }
        
        // returns the City Active Record
        return $this->item;
    }
}