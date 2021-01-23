<?php
/**
 * PublicView
 *
 * @version    1.0
 * @package    control
 * @subpackage public
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PublicView extends TPage
{
    public function __construct()
    {
        parent::__construct();
        
        //AdiantiCoreApplication::loadPage('OfertaList', 'abrir');
    }
}
