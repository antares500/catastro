<img src="https://github.com/antares500/catastro/blob/5cf75060edad19653542f3df31f40aed1d54009e/logo-catastro.png" alt="Logo catastro" width="100"/>

# Catastro 1.0.0
Librería PHP para consultar el Catastro desde PHP (Inspirado en [PyCatastro](https://github.com/gisce/pycatastro))
Para más información puedes revisar la [documentacion oficial del catastro](https://ovc.catastro.meh.es/ovcservweb/ovcswlocalizacionrc/ovccoordenadas.asmx)

## Funciones
- **Provincia**: Proporciona un listado de todas las provincias.
- **Municipio**: Listado de municipios de una provincia
- **Numero**: Obtiene la referencia catastral
- **Via**: Listado de vías de un municipio
- **NumeroVia**: Listado de numeros de una via
- **RC**: (ReferenciaCatastral) Obtiene la referencia catastral
- **DCNP**: (DatosCatastralesNoProtegidos) Obtiene los datos catastrales no protegidos de un inmueble. Obtiene los datos a partir de:
    - Referencia catastral [RC]
    - Poligono y parcela [Poligono, Parcela]
    - Sigla, calle y numero [Sigla, Calle, Numero, (Bloque, Escalera, Planta, Puerta)]
    - Tipo de via, calle y numero [TipoVia, Calle, Numero, (Bloque, Escalera, Planta, Puerta)]
    - Coordenadas X e Y [Coordenada_X, Coordenada_Y]
- **RCCOOR**: (ReferenciaCatastralCoordenadas) A partir de unas coordenadas se obtiene la referencia catastral
- **RCCOOR**: (ReferenciaCatastralCoordenadas) A partir de unas coordenadas se obtiene la referencia catastral
- **RCCOOR_Distancia**: (ReferenciaCatastral Cercana) Obtiene la lista de referencias catastrales cercanas a partir de unas coordenadas
- **CPMRC**: (ConsultaProvinciaMunicipioReferenciaCatastral) Obtiene las coordenadas de una parcela a partir de su referencia catastral
- **GET_GEOJSON_3D**: Obtencion deel geoJSON para construir el modelo 3D

## demo
```php
$catastro = new Catastro();

$provincias = $catastro->Provincia();
print_r($provincias);


```

## License

Apache License 2.0
