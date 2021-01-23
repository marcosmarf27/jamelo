<?php
/**
 * SystemRegistrationForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemRegistrationForm extends TPage
{
    protected $form; // form
    protected $program_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_registration');
        $this->form->setFormTitle( _t('User registration') );
        
        // create the form fields
        $login      = new TEntry('login');
        $name       = new TEntry('name');
        $telefone      = new TEntry('telefone');
        $email      = new TEntry('email');
        $password   = new TPassword('password');
        $repassword = new TPassword('repassword');
        
        $this->form->addAction('Continue',  new TAction([$this, 'onSave']), 'fas:arrow-right green');
       // $this->form->addAction( _t('Clear'), new TAction([$this, 'onClear']), 'fa:eraser red' );
        //$this->form->addActionLink( _t('Back'),  new TAction(['LoginForm','onReload']), 'far:arrow-alt-circle-left blue' );
        
        $login->addValidation( _t('Login'), new TRequiredValidator);
        $name->addValidation( _t('Name'), new TRequiredValidator);
        $email->addValidation( _t('Email'), new TRequiredValidator);
        $password->addValidation( _t('Password'), new TRequiredValidator);
        $repassword->addValidation( _t('Password confirmation'), new TRequiredValidator);
        
        // define the sizes
        $name->setSize('100%');
        $login->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $email->setSize('100%');

        $login->forceUpperCase();
        $name->forceUpperCase();
        $email->forceUpperCase();
        
        $telefone->setMask('(99)99999-9999', true);
        $telefone->placeholder = '(00) 00000-0000';
        $telefone->addValidation('Telefone', new TMinLengthValidator, array(9));

        $this->form->addFields( [new TLabel(_t('Login'))],    [$login] );
        $this->form->addFields( [new TLabel(_t('Name'))],     [$name] );
        $this->form->addFields( [new TLabel('Whatsapp')],     [$telefone] );
        $this->form->addFields( [new TLabel(_t('Email'))],    [$email] );
        $this->form->addFields( [new TLabel(_t('Password'))], [$password] );
        $this->form->addFields( [new TLabel(_t('Password confirmation'))], [$repassword] );
        
        // add the container to the page
        $wrapper = new TElement('div');
        $wrapper->style = 'margin:auto; margin-top:30px;max-width:800px;';
        $wrapper->id    = 'login-wrapper';
        $wrapper->add($this->form);
        
        // add the wrapper to the page
        //parent::add($wrapper);

        $pagestep = new TPageStep;
        $pagestep->addItem('Cadastro');
        $pagestep->addItem('Informações básicas');
        $pagestep->addItem('Endereço');
        $pagestep->addItem('Pagamento');
        $pagestep->select('Informações básicas');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add( $pagestep );
        $vbox->add( $wrapper );
        parent::add($vbox);
    }
    
    /**
     * Clear form
     */
    public function onClear()
    {
        $this->form->clear( true );
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {
        try
        {
            $ini = AdiantiApplicationConfig::get();
            if ($ini['permission']['user_register'] !== '1')
            {
                throw new Exception( _t('The user registration is disabled') );
            }
            
            // open a transaction with database 'permission'
            TTransaction::open('permission');
            
            if( empty($param['login']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($param['name']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Name')));
            }
            
            if( empty($param['email']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Email')));
            }
            
            if( empty($param['password']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
            }
            
            if( empty($param['repassword']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password confirmation')));
            }
            
            if (SystemUser::newFromLogin($param['login']) instanceof SystemUser)
            {
                throw new Exception(_t('An user with this login is already registered'));
            }
            
            if (SystemUser::newFromEmail($param['email']) instanceof SystemUser)
            {
                throw new Exception(_t('An user with this e-mail is already registered'));
            }
            
            if( $param['password'] !== $param['repassword'] )
            {
                throw new Exception(_t('The passwords do not match'));
            }
            
            $object = new SystemUser;
            $object->active = 'Y';
            $object->fromArray( $param );
            $object->password = md5($object->password);
            $object->frontpage_id = $ini['permission']['default_screen'];
            $object->clearParts();
            $object->store();
            
            $default_groups = explode(',', $ini['permission']['default_groups']);
            
            if( count($default_groups) > 0 )
            {
                foreach( $default_groups as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }
            
            $default_units = explode(',', $ini['permission']['default_units']);
            
            if( count($default_units) > 0 )
            {
                foreach( $default_units as $unit_id )
                {
                    $object->addSystemUserUnit( new SystemUnit($unit_id) );
                }
            }
            
            TTransaction::close(); // close the transaction

       
            TToast::show('success', 'Login e cadastro criado com sucesso!', 'bottom center', 'far:check-circle' );
            TSession::setValue('passologin', $object);
            AdiantiCoreApplication::loadPage('EnderecoForm', 'onReload');
            //$pos_action = new TAction(['LoginForm', 'onLoad']);
            //new TMessage('info', _t('Account created'), $pos_action); // shows the success message
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
