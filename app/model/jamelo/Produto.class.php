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
class Produto extends TRecord
{
    const TABLENAME = 'produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('preco');
        parent::addAttribute('imagem');
        parent::addAttribute('categoria_id');
        parent::addAttribute('nome');
        parent::addAttribute('cor');
    }
}
