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
class Endereco extends TRecord
{
    const TABLENAME = 'endereco';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $localidade;
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('rua');
        parent::addAttribute('numero');
        parent::addAttribute('bairro');
        parent::addAttribute('cidade_id');
        parent::addAttribute('estado_id');
        parent::addAttribute('lat');
        parent::addAttribute('lon');
        parent::addAttribute('obs');
        parent::addAttribute('complemento');
        parent::addAttribute('system_user_id');
        parent::addAttribute('tipo');
        parent::addAttribute('localidade_id');
    }

    function get_localidade()
    {
        // instantiates City, load $this->city_id
        if (empty($this->localidade))
        {
            $this->localidade = new Localidade($this->localidade_id);
        }
        
        // returns the City Active Record
        return $this->localidade;
    }
  
}