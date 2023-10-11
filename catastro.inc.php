<?php
// Consultar el catastro - 10 de octubre de 2023

class Catastro {

    public $url = 'http://ovc.catastro.meh.es/ovcservweb/OVCSWLocalizacionRC';

    // Proporciona un listado de todas las provincias.
    function Provincia(){
        $url = $this->url.'/OVCCallejero.asmx/ConsultaProvincia';
        $response = $this->call($url);
        $prov = [];
        foreach ( $response['provinciero']['prov'] as $p ) $prov[$p['cpine']] = $p['np'];
        return $prov;
    }

    // Listado de municipios de una provincia
    function Municipio($provincia, $municipio = null){
        $url = $this->url.'/OVCCallejero.asmx/ConsultaMunicipio';
        $data = [
            'Provincia' => $provincia,
            'Municipio' => $municipio ?? ''
        ];
        $return = $this->call($url, $data);
        return $return['municipiero']['muni'];
    }

    // Obtiene la referencia catastral
    function Numero($provincia, $municipio, $tipovia, $nombrevia, $numero){
        $url = $this->url.'/OVCCallejero.asmx/ConsultaNumero';
        $data = [
            'Provincia' => $provincia,
            'Municipio' => $municipio,
            'TipoVia' => $tipovia,
            'NombreVia' => $nombrevia,
            'Numero' => $numero
        ];
        return $this->call($url, $data);
    }

    // Listado de vías de un municipio
    function Via($provincia, $municipio, $tipovia = null, $nombrevia = null){
        return $this->RC($provincia, $municipio, $tipovia, $nombrevia);
    }

    // Listado de numeros de una via
    function NumeroVia($provincia, $municipio, $tipovia = null, $nombrevia = null, $numero = null){
        return $this->RC($provincia, $municipio, $tipovia, $nombrevia, $numero);
    }

    // (ReferenciaCatastral) Obtiene la referencia catastral
    function RC($provincia, $municipio, $tipovia = null, $nombrevia = null, $numero = null){
        $url = $this->url.'/OVCCallejero.asmx/ConsultaVia';
        $data = [
            'Provincia' => $provincia,
            'Municipio' => $municipio
        ];
        if($tipovia) $data['TipoVia'] = $tipovia;
        if($nombrevia) $data['NombreVia'] = $nombrevia;
        if($numero) $data['Numero'] = $numero;

        return $this->call($url, $data);
    }

    /*
    (DatosCatastralesNoProtegidos) Obtiene los datos catastrales no protegidos de un inmueble:

    Obttiene los datos a partir de:
    - Referencia catastral [RC]
    - Poligono y parcela [Poligono, Parcela]
    - Sigla, calle y numero [Sigla, Calle, Numero, (Bloque, Escalera, Planta, Puerta)]
    - Tipo de via, calle y numero [TipoVia, Calle, Numero, (Bloque, Escalera, Planta, Puerta)]
    - Coordenadas X e Y [Coordenada_X, Coordenada_Y]
    */

    function DCNP($provincia, $municipio, $attr){
        if(isset($attr['rc'])){
            $url = $this->url.'/OVCCallejero.asmx/Consulta_DNPRC';
            $data = [
                'Provincia' => $provincia,
                'Municipio' => $municipio,
                'RC'        => $attr['RC']
            ];
        } else if(isset($attr['Poligono']) && isset($attr['Parcela'])){
            $url = $this->url.'/OVCCallejero.asmx/Consulta_DNPPP';
            $data = [
                'Provincia' => $provincia,
                'Municipio' => $municipio,
                'Poligono' => $attr['Poligono'],
                'Parcela' => $attr['Parcela']
            ];
        } else if(isset($attr['Sigla']) && isset($attr['Calle']) && isset($attr['Numero'])){
            $url = $this->url.'/OVCCallejero.asmx/Consulta_DNPLOC';
            $data = [
                'Provincia'     => $provincia,
                'Municipio'     => $municipio,
                'Bloque'        => $attr['Bloque'] ?? '',
                'Escalera'      => $attr['escalera'] ?? '',
                'Planta'        => $attr['planta'] ?? '',
                'Puerta'        => $attr['puerta'] ?? '',
                'Coordenada_X'  => $attr['Coordenada_X'] ?? '',
                'Coordenada_Y'  => $attr['Coordenada_Y'] ?? ''
            ];
            if(isset($attr['Sigla']) && isset($attr['Calle']) && isset($attr['Numero'])){
                $data['Sigla'] = $attr['Sigla'];
                $data['Calle'] = $attr['Calle'];
                $data['Numero']= $attr['Numero'];
            } else if(isset($attr['TipoVia']) && isset($attr['Calle']) && isset($attr['Numero'])){
                $data['TipoVia'] = $attr['TipoVia'];
                $data['NombreVia'] = $attr['Calle'];
                $data['Numero']= $attr['Numero'];
            } else if(isset($attr['Calle'])){
                $data['NombreVia'] = $attr['Calle'];
            }
        } else {
            return ['error' => 'No se han proporcionado los datos necesarios para la consulta'];
        }

        return $this->call($url, $data);
    }

    // (ReferenciaCatastralCoordenadas) A partir de unas coordenadas se obtiene la referencia catastral
    function RCCOOR($srs, $x, $y){
        $url = $this->url.'/OVCCoordenadas.asmx/Consulta_RCCOOR';
        $data = [
            'SRS' => is_string($srs) ? $srs : 'EPSG:'.$srs,
            'Coordenada_X' => $x,
            'Coordenada_Y' => $y
        ];
        return $this->call($url, $data);
    }

    // (ReferenciaCatastral Cercana) Obtiene la lista de referencias catastrales cercanas a partir de unas coordenadas
    function RCCOOR_Distancia($srs, $x, $y){
        $url = $this->url.'/OVCCoordenadas.asmx/Consulta_RCCOOR_Distancia';
        $data = [
            'SRS' => is_string($srs) ? $srs : 'EPSG:'.$srs,
            'Coordenada_X' => $x,
            'Coordenada_Y' => $y
        ];
        $response = $this->call($url, $data);
        return $response['coordenadas_distancias'];
    }

    // (ConsultaProvinciaMunicipioReferenciaCatastral) Obtiene las coordenadas de una parcela a partir de su referencia catastral
    function CPMRC($provincia, $municipio, $srs, $rc){
        $url = $this->url.'/OVCCoordenadas.asmx/Consulta_CPMRC';
        $data = [
            'Provincia' => $provincia,
            'Municipio' => $municipio,
            'SRS' => is_string($srs) ? $srs : 'EPSG:'.$srs,
            'RC' => $rc
        ];
        return $this->call($url, $data);
    }

    // Obtencion del geoJSON para construir el modelo 3D
    function GET_GEOJSON_3D($RC){
        $url = 'https://www1.sedecatastro.gob.es/Cartografia/FXCC/Visor3D.aspx?refcat='.$RC;        
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => [
                "Accept: */*",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Host: www1.sedecatastro.gob.es",
                "User-Agent: PostmanRuntime/7.11.0",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
            ]
        ]);

        $response = curl_exec($ch);
        // obtengo los datos necesarios
        $exp = '/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*)" \/>/';
        preg_match($exp, $response, $matches);
        $viewstate = $matches[1];
        $viewstate = base64_decode($viewstate);
        $ini = strpos($viewstate, '<script>var edificio3D_geojson=') + strlen('<script>var edificio3D_geojson=');
        $viewstate = substr($viewstate, $ini, strpos($viewstate, '};', $ini) - $ini + 1);
        $viewstate = json_decode($viewstate, 1);

        // obtengo los datos de la parcela
        // $exp = '/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*)" \/>/';
        // preg_match($exp, $response, $matches);
        // $viewstategenerator = $matches[1];
        // $viewstategenerator = base64_decode($viewstategenerator);
        // $exp = '/<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*)" \/>/';
        // preg_match($exp, $response, $matches);
        // $eventvalidation = $matches[1];

        return $viewstate;
    }
    
    // function DNPOL($provincia, $municipio, $poligono, $parcela){
    //     $url = $this->url.'/OVCCoordenadas.asmx/Consulta_DNPOL';
    //     $data = [
    //         'Provincia' => $provincia,
    //         'Municipio' => $municipio,
    //         'Poligono' => $poligono,
    //         'Parcela' => $parcela
    //     ];
    //     return $this->call($url, $data);
    // }

    /////////////////////////////////////////////

    // realiza una llamada a catastro con los parametros indicados
    // devuelve un array con los datos necesarios
    function call($url, $data=[], $expire=3600){
        // creo la carpeta de cache
        if(!is_dir('cache')) mkdir('cache', 0777, true);

        $file = 'cache/'.md5($url).'.json';

        if ( file_exists($file) && filemtime($file) > time() - $expire ){
            $response = file_get_contents($file);
        } else {
            $ch = curl_init();
            if($data)    $url = $url.'?'.http_build_query($data);
            $curl = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/xml',
                    'Accept-Encoding: gzip, deflate',
                    'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: ovc.catastro.meh.es',
                    'Origin: http://ovc.catastro.meh.es',
                    'Pragma: no-cache',
                    'Referer: http://ovc.catastro.meh.es/',
                    'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'
                ],
                CURLOPT_ENCODING => 'gzip, deflate',
                CURLOPT_SSL_VERIFYPEER => false,
            ];
            curl_setopt_array($ch, $curl);

            $response = curl_exec($ch);
            curl_close($ch);

            file_put_contents($file, $response);
        }

        // print_r($response);

        // xml a array
        $response = simplexml_load_string($response);
        $response = json_decode(json_encode($response), 1);

        return $response;
    }
}