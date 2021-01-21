<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TImageCropper;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Form\TColor;
use Adianti\Wrapper\BootstrapFormBuilder;
/**
 * Product Form
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ProdutoForm extends TPage
{
    protected $form;
    
    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
    
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Product');
        $this->form->setFormTitle(_t('Product'));
        $this->form->setClientValidation(true);
        
        // create the form fields
        $id          = new TEntry('id');
        $description = new TEntry('descricao');
        $nome = new TEntry('nome');
        $cor = new TColor('cor');
        $categoria    = new TDBCombo('categoria_id', 'jamelo', 'Categoria', 'id', 'nome');
        $sale_price  = new TEntry('preco');

      
       
        $photo_path  = new TImageCropper('imagem');
        $photo_path->setSize(300, 300);
        $photo_path->setCropSize(300, 300);
        $photo_path->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        $photo_path->placeholder = "clique aqui";
       
        
        // allow just these extensions
        $photo_path->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
       
        
        // enable progress bar, preview
        $photo_path->enableFileHandling();
       // $photo_path->enablePopover();
        
        // enable progress bar, preview, and gallery mode
     
        
        $id->setEditable( FALSE );
        
        $sale_price->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving
        
        // add the form fields
        $this->form->addFields( [new TLabel('ID', 'red')],          [$id] );
        $this->form->addFields( [new TLabel('Nome', 'red')], [$nome] );
        $this->form->addFields( [new TLabel('Descrição', 'red')], [$description] );
        $this->form->addFields([new TLabel('Preço', 'red')],  [$sale_price], [new TLabel('Categoria', 'red')],  [$categoria]);
      
        $this->form->addFields( [new TLabel('Imagem')],  [$photo_path], [new TLabel('Cor')],  [$cor] );
       
        
        $id->setSize('50%');
        
        $description->addValidation('Description', new TRequiredValidator);
       
        $sale_price->addValidation('Sale Price', new TRequiredValidator);
      
        
        // add the actions
        $this->form->addAction( 'Save', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Clear', new TAction([$this, 'onEdit']), 'fa:eraser red');
      $this->form->addActionLink( 'List', new TAction(['ProdutoList', 'onReload']), 'fa:table blue');

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);

        parent::add($vbox);
    }
    
    /**
     * Overloaded method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open('jamelo');
            
            // form validations
            $this->form->validate();
            
            // get form data
            $data   = $this->form->getData();
            
            // store product
            $object = new Produto();
            $object->fromArray( (array) $data);
            $object->store();
            
            // copy file to target folder
            $this->saveFile($object, $data, 'imagem', 'files/images');
            
           
            
            // send id back to the form
            $data->id = $object->id;
            $this->form->setData($data);
            
            TTransaction::close();
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e)
        {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                TTransaction::open('jamelo');
                $object = new Produto( $param['key'] );
               // $object->images = ProductImage::where('product_id', '=', $param['key'])->getIndexedArray('id', 'image');
                $this->form->setData($object);
                TTransaction::close();
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
