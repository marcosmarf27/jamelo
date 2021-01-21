<?php
class TesteApp extends TWindow
{
    public function __construct()
    {
        parent::__construct();
        parent::setTitle('Enviar mensagem de whatsapp');
        parent::removePadding();
        parent::setSize(0.8, 650);
        
        $iframe = new TElement('iframe');
        $iframe->id = "iframe_external";
        $iframe->src = "https://api.whatsapp.com/send?phone=5588992798233&text=Ola";
        $iframe->frameborder = "0";
        $iframe->scrolling = "yes";
        $iframe->width = "100%";
        $iframe->height = "600px";
        
        parent::add($iframe);
    }
}