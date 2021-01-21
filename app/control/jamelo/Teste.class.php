<?php

use Adianti\Control\TPage;

class Teste extends TPage {

    public function __construct()
    {
        parent::__construct();

        $gmaps = new gMaps();

        // Pega os dados (latitude, longitude e zoom) do endereço:
        $endereco = 'Av. Brasil, 1453, Rio de Janeiro, RJ';
        $dados = $gmaps->geoLocal($endereco);

        // Exibe os dados encontrados:
        print_r($dados);

        echo "ertyetrytytr";
        
    }
}