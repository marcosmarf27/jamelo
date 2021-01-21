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
class Fidelidade extends TRecord
{
    const TABLENAME = 'fidelidade';
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
        parent::addAttribute('system_user_id');
        parent::addAttribute('pedido_id');
        parent::addAttribute('valorpedido');
        parent::addAttribute('pontovalor');
        parent::addAttribute('ativo');
 
    
    }

    /* function get_cliente()
    {
        // instantiates City, load $this->city_id
        if (empty($this->cliente))
        {
            $this->cliente = new SystemUser($this->system_user_id);
        }
        
        // returns the City Active Record
        return $this->cliente;
    } */
}