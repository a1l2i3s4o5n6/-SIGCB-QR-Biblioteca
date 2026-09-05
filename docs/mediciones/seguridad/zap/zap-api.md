# ZAP Scanning Report

ZAP by [Checkmarx](https://checkmarx.com/).


## Summary of Alerts

| Risk Level | Number of Alerts |
| --- | --- |
| High | 0 |
| Medium | 1 |
| Low | 2 |
| Informational | 3 |




## Insights

| Level | Reason | Site | Description | Statistic |
| --- | --- | --- | --- | --- |
| Low | Warning |  | ZAP errors logged - see the zap.log file for details | 4    |
| Low | Warning |  | ZAP warnings logged - see the zap.log file for details | 4    |
| Low | Exceeded High | http://api:8080 | Percentage of responses with status code 4xx | 99 % |
| Info | Informational | http://api:8080 | Percentage of responses with status code 2xx | 1 % |
| Info | Informational | http://api:8080 | Percentage of endpoints with content type application/json | 1 % |
| Info | Informational | http://api:8080 | Percentage of endpoints with content type application/problem+json | 99 % |
| Info | Informational | http://api:8080 | Percentage of endpoints with method DELETE | 4 % |
| Info | Informational | http://api:8080 | Percentage of endpoints with method GET | 65 % |
| Info | Informational | http://api:8080 | Percentage of endpoints with method POST | 17 % |
| Info | Informational | http://api:8080 | Percentage of endpoints with method PUT | 12 % |
| Info | Informational | http://api:8080 | Count of total endpoints | 217    |
| Info | Informational | http://api:8080 | Percentage of slow responses | 2 % |







## Alerts

| Name | Risk Level | Number of Instances |
| --- | --- | --- |
| Buffer Overflow | Medium | 1 |
| A Server Error response code was returned by the server | Low | 1 |
| Cross-Origin-Resource-Policy Header Missing or Invalid | Low | 2 |
| A Client Error response code was returned by the server | Informational | 219 |
| Authentication Request Identified | Informational | 2 |
| Non-Storable Content | Informational | Systemic |




## Alert Detail



### [ Buffer Overflow ](https://www.zaproxy.org/docs/alerts/30001/)



##### Medium (Medium)

### Description

Buffer overflow errors are characterized by the overwriting of memory spaces of the background web process, which should have never been modified intentionally or unintentionally. Overwriting values of the IP (Instruction Pointer), BP (Base Pointer) and other registers causes exceptions, segmentation faults, and other process errors to occur. Usually these errors end execution of the application in an unexpected way.

* URL: http://api:8080/api/auth/register
  * Node Name: `http://api:8080/api/auth/register ()({nombre,email,password,telefono,rolId})`
  * Method: `POST`
  * Parameter: `rolId`
  * Attack: `UtauIHLuNgZyehHLfZyHSnCWCuuqvVPtRbcGBBMgdeYpVXOpxXRqAxFpOLRoDlBkhDwfFwfOwtwUWulZQyIFOoRPOUsgoxgvQrylCSfmgnaRutQVjSATorHiBrnYyOHDywEoruwunwLPSDRwfIyuLvnXgPlSJyogMQWxfAqHZoMwWmLdfMNFucBcnFohGbNyUJhqRdhxGgyWLcVmsXbUAOYgfJnfoBQqZxnOkxakgyLSyWrqAayBOQlsQdmrwvUKEscPLASElqSUecROmGdClpdXLIKxPYUxFsFxsvLXURMOIlGBNQxTCkLbDYOrxrPgIYoefjUrLCAQqkTUXOjGxkmIqeQskOSuTkEPRnxtPILVjjxjmMLLlvJweHCIHbDIALbMgHygGAYpBYhNmxPCTCPYopOyGoeTlRqTmIHysEZKxcuHuyFNUFMGHsrmNmpWIPLfVMSElDcDKJoWbMCVGRpNGefFFnVXWUiQNIRDHYrUnyNGkAZgbkZHhMMGCelGHSICofWqqBMJcScgFUMirVxcnIVJUuTSWJfvwYPNaYEWntsmGRYawciInBpxnhaOXLxSNfdFNDuhNEdmWbrTjaTDewnkhcDqrTKPbFklTtXjoGRTBlFLgkaDYRgBBKYGgIetdyBeyXfwmTqNwxLTBoXrmdTpKKZrFyjpLicUEEVwGOCrkOwxYNhcjCdjLYwqKUChxpXmkJcrMdBbhevSNkNAalUBiNFCDwkiIXCCQDQAOLjWtZldRcAUvPKmhhyvMjQLNdRjDkQYwSaFBJcDnLWnRoSouMVwnLiSEuUQiNqSusaNxPIKgjVMdodpHPwEpfhQpAbpommUQmBVEdbKtIAgonASRHBBFXsXKEWJNAInqHuKSVmLkrQkygMBmDjtveSAqfnaJBndPmQBaSDLmwhyasMSkKymgoYGTDYcnQVKdXxOgAJVpaJikshclGxhKjEIVuSVJUrdjKBSTaiWirVTbsvRaAMRduDLyPOWXLGVHapcmdgoRbFOuaPWetLnbZaAJwibDNwepbfHaZtxFIswnRlMsZuGSwVQDfSyWqDaoZKUcVtxsejEIwnxZCFLWrfQMHtDTwOjvhwXWunQppBdHfVKJSdEggGyhtFIHiQKkHAFEXchfNClJMGHgaXWUjEPYpEvpyGDLLrjfvIfHcFkRcXrDItKQEWyOSkXWEVSwVsPryhhQyaJOfRmgvVcEBCXxcSrFHTinJgqGpGeTqxMSUgTQNeVfvdeaxJRAYOPfmjeOQKcIgEMljnEZvMpuSnxSjDcvVGdFcQWKUsWKgNJOiDIqRAaEguOdYTDjBTZYUQRtvmCjlFsSfEZCsaDscrorFxUUkfvSFtLrGhdemiGZwKpQXhaVlLOCUsuCHCkSJUYgwPWNBrZXnNDamxsPLAZHXqFmSVHwoDGSFSgnKNvReNaFXicPEHFVsaSjLTystCZrMKDEZvNfPSOAHLEhxohtIDcXCjIZHpqBPGawoxhQLQnahwiLjlZXKOqDbytQErjPCiBTcXmFZNdrjBjFLlAdIyHUawEBjoOJhGmCFtTEcIodIXEbiEdjguQqnNqDSRmsPXSOkoiOIjqRidPrTYYBwDnIKctgPQLgpiSbcKsuLGPOykwlvbGNSCdwUHCkUvDDcOyaoRZgtweOBjGkAsdrtAENLSGZwDpknDPoglkKnrIwrHOirDYjRvByCroCWFnoOOJPRRLyhnUSgWYXtLZZBcpAnnkCBOwXLyujnaVRpmnBaTVbQlBhTFRtcVksVARbQmLvjWsIqqWUKYdBEdpVErkyDNkZCoVLMvTxiaLwLbkmGBQuaRwbucWorvgyZQapTinRTsTMkRElPAWfWhNwpUOVbnoVTrocCWAtXdfesWepAtqHcTsmOAfbomvLZcJqggbvJcNGtMhOhbgqiaQCstveSmfaxWyvGFkLLAPESNCQEVgdIsVWAVFalarZsPWrEfZPmrKFGPXDtRIbhUIgHXSSehtdIZACJSajXhnplhXoAZuobJyHyGIJKDbRDjASCVsiepZptJFIfJccovNQbRnKPaWwLyfwjuPXmVuwgUFXNZWrIyKZdtJktvNxZdyFKkp`
  * Evidence: `Connection: close`
  * Other Info: `Potential Buffer Overflow. The script closed the connection and threw a 500 Internal Server Error.`


Instances: 1

### Solution

Rewrite the background program using proper return length checking. This will require a recompile of the background executable.

### Reference


* [ https://owasp.org/www-community/attacks/Buffer_overflow_attack ](https://owasp.org/www-community/attacks/Buffer_overflow_attack)


#### CWE Id: [ 120 ](https://cwe.mitre.org/data/definitions/120.html)


#### WASC Id: 7

#### Source ID: 1

### [ A Server Error response code was returned by the server ](https://www.zaproxy.org/docs/alerts/100000/)



##### Low (High)

### Description

A response code of 500 was returned by the server.
This may indicate that the application is failing to handle unexpected input correctly.
Raised by the 'Alert on HTTP Response Code Error' script

* URL: http://api:8080/api/auth/register
  * Node Name: `http://api:8080/api/auth/register ()({nombre,email,password,telefono,rolId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `500`
  * Other Info: ``


Instances: 1

### Solution



### Reference



#### CWE Id: [ 388 ](https://cwe.mitre.org/data/definitions/388.html)


#### WASC Id: 20

#### Source ID: 4

### [ Cross-Origin-Resource-Policy Header Missing or Invalid ](https://www.zaproxy.org/docs/alerts/90004/)



##### Low (Medium)

### Description

Cross-Origin-Resource-Policy header is an opt-in header designed to counter side-channels attacks like Spectre. Resource should be specifically set as shareable amongst different origins.

* URL: http://api:8080/api-docs
  * Node Name: `http://api:8080/api-docs`
  * Method: `GET`
  * Parameter: `Cross-Origin-Resource-Policy`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://api:8080/api/auth/logout
  * Node Name: `http://api:8080/api/auth/logout`
  * Method: `POST`
  * Parameter: `Cross-Origin-Resource-Policy`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``


Instances: 2

### Solution

Ensure that the application/web server sets the Cross-Origin-Resource-Policy header appropriately, and that it sets the Cross-Origin-Resource-Policy header to 'same-origin' for all web pages.
'same-site' is considered as less secured and should be avoided.
If resources must be shared, set the header to 'cross-origin'.
If possible, ensure that the end user uses a standards-compliant and modern web browser that supports the Cross-Origin-Resource-Policy header (https://caniuse.com/mdn-http_headers_cross-origin-resource-policy).

### Reference


* [ https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Cross-Origin-Embedder-Policy ](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Cross-Origin-Embedder-Policy)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 14

#### Source ID: 3

### [ A Client Error response code was returned by the server ](https://www.zaproxy.org/docs/alerts/100000/)



##### Informational (High)

### Description

A response code of 401 was returned by the server.
This may indicate that the application is failing to handle unexpected input correctly.
Raised by the 'Alert on HTTP Response Code Error' script

* URL: http://api:8080/api/categorias/10
  * Node Name: `http://api:8080/api/categorias/10`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias/10/
  * Node Name: `http://api:8080/api/categorias/10/`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales/10
  * Node Name: `http://api:8080/api/editoriales/10`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales/10/
  * Node Name: `http://api:8080/api/editoriales/10/`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/10
  * Node Name: `http://api:8080/api/libros/10`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/10/
  * Node Name: `http://api:8080/api/libros/10/`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas/10
  * Node Name: `http://api:8080/api/reservas/10`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas/10/
  * Node Name: `http://api:8080/api/reservas/10/`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/10
  * Node Name: `http://api:8080/api/usuarios/10`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/10/
  * Node Name: `http://api:8080/api/usuarios/10/`
  * Method: `DELETE`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080
  * Node Name: `http://api:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/
  * Node Name: `http://api:8080/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/852044903617926102
  * Node Name: `http://api:8080/852044903617926102`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api
  * Node Name: `http://api:8080/api`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api-docs/
  * Node Name: `http://api:8080/api-docs/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/
  * Node Name: `http://api:8080/api/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/6038247929212166945
  * Node Name: `http://api:8080/api/6038247929212166945`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/auditoria%3FusuarioId=10&desde=desde&hasta=hasta&pageable=
  * Node Name: `http://api:8080/api/auditoria (desde,hasta,pageable,usuarioId)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/auditoria/
  * Node Name: `http://api:8080/api/auditoria/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/auth
  * Node Name: `http://api:8080/api/auth`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/auth/
  * Node Name: `http://api:8080/api/auth/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/auth/3724397710631937142
  * Node Name: `http://api:8080/api/auth/3724397710631937142`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/auth/actuator/health
  * Node Name: `http://api:8080/api/auth/actuator/health`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/auth/me
  * Node Name: `http://api:8080/api/auth/me`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/auth/me/
  * Node Name: `http://api:8080/api/auth/me/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/autores
  * Node Name: `http://api:8080/api/autores`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/autores%3Fpageable=
  * Node Name: `http://api:8080/api/autores (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/autores/
  * Node Name: `http://api:8080/api/autores/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/autores/7556817103998963816
  * Node Name: `http://api:8080/api/autores/7556817103998963816`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/carreras%3FfacultadId=10
  * Node Name: `http://api:8080/api/carreras (facultadId)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/carreras/
  * Node Name: `http://api:8080/api/carreras/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias
  * Node Name: `http://api:8080/api/categorias`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias%3Fpageable=
  * Node Name: `http://api:8080/api/categorias (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias/
  * Node Name: `http://api:8080/api/categorias/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias/2089205490134518044
  * Node Name: `http://api:8080/api/categorias/2089205490134518044`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/configuracion
  * Node Name: `http://api:8080/api/configuracion`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/configuracion/
  * Node Name: `http://api:8080/api/configuracion/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/configuracion/3374302808347702750
  * Node Name: `http://api:8080/api/configuracion/3374302808347702750`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/dashboard
  * Node Name: `http://api:8080/api/dashboard`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/dashboard/
  * Node Name: `http://api:8080/api/dashboard/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/dashboard/3691079327144273395
  * Node Name: `http://api:8080/api/dashboard/3691079327144273395`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/dashboard/resumen%3Fdesde=desde&hasta=hasta
  * Node Name: `http://api:8080/api/dashboard/resumen (desde,hasta)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/dashboard/resumen/
  * Node Name: `http://api:8080/api/dashboard/resumen/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/dashboard/stats
  * Node Name: `http://api:8080/api/dashboard/stats`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/dashboard/stats/
  * Node Name: `http://api:8080/api/dashboard/stats/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales
  * Node Name: `http://api:8080/api/editoriales`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales%3Fpageable=
  * Node Name: `http://api:8080/api/editoriales (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales/
  * Node Name: `http://api:8080/api/editoriales/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales/3965418308347045245
  * Node Name: `http://api:8080/api/editoriales/3965418308347045245`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/facultades
  * Node Name: `http://api:8080/api/facultades`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/facultades/
  * Node Name: `http://api:8080/api/facultades/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario
  * Node Name: `http://api:8080/api/inventario`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario%3Festado=estado
  * Node Name: `http://api:8080/api/inventario (estado)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario/
  * Node Name: `http://api:8080/api/inventario/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario/4333057273583603930
  * Node Name: `http://api:8080/api/inventario/4333057273583603930`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario/buscar%3Fcodigo=codigo
  * Node Name: `http://api:8080/api/inventario/buscar (codigo)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario/buscar/
  * Node Name: `http://api:8080/api/inventario/buscar/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario/disponibles%3FlibroId=10
  * Node Name: `http://api:8080/api/inventario/disponibles (libroId)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/inventario/disponibles/
  * Node Name: `http://api:8080/api/inventario/disponibles/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros
  * Node Name: `http://api:8080/api/libros`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros%3Fq=q&categoriaId=10&editorialId=10&anio=10&soloDisponibles=true&pageable=
  * Node Name: `http://api:8080/api/libros (anio,categoriaId,editorialId,pageable,q,soloDisponibles)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/
  * Node Name: `http://api:8080/api/libros/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/10
  * Node Name: `http://api:8080/api/libros/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/10/
  * Node Name: `http://api:8080/api/libros/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/3884009955334154835
  * Node Name: `http://api:8080/api/libros/3884009955334154835`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/buscar%3Fq=q&pageable=
  * Node Name: `http://api:8080/api/libros/buscar (pageable,q)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/buscar/
  * Node Name: `http://api:8080/api/libros/buscar/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas
  * Node Name: `http://api:8080/api/multas`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas%3Fpagada=true&pageable=
  * Node Name: `http://api:8080/api/multas (pagada,pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/
  * Node Name: `http://api:8080/api/multas/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/10
  * Node Name: `http://api:8080/api/multas/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/10/
  * Node Name: `http://api:8080/api/multas/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/10/9115247226695845302
  * Node Name: `http://api:8080/api/multas/10/9115247226695845302`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/3460918175525437511
  * Node Name: `http://api:8080/api/multas/3460918175525437511`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/mis%3Fpageable=
  * Node Name: `http://api:8080/api/multas/mis (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/mis/
  * Node Name: `http://api:8080/api/multas/mis/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/usuario
  * Node Name: `http://api:8080/api/multas/usuario`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/usuario/
  * Node Name: `http://api:8080/api/multas/usuario/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/usuario/10%3Fpageable=
  * Node Name: `http://api:8080/api/multas/usuario/10 (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/usuario/10/
  * Node Name: `http://api:8080/api/multas/usuario/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/usuario/8782514334481481488
  * Node Name: `http://api:8080/api/multas/usuario/8782514334481481488`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones
  * Node Name: `http://api:8080/api/notificaciones`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones%3Fpageable=
  * Node Name: `http://api:8080/api/notificaciones (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/
  * Node Name: `http://api:8080/api/notificaciones/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/10
  * Node Name: `http://api:8080/api/notificaciones/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/10/
  * Node Name: `http://api:8080/api/notificaciones/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/10/8912992273584471668
  * Node Name: `http://api:8080/api/notificaciones/10/8912992273584471668`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/4523020563376323225
  * Node Name: `http://api:8080/api/notificaciones/4523020563376323225`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/no-leidas
  * Node Name: `http://api:8080/api/notificaciones/no-leidas`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/no-leidas/
  * Node Name: `http://api:8080/api/notificaciones/no-leidas/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/todas%3Fleida=true&pageable=
  * Node Name: `http://api:8080/api/notificaciones/todas (leida,pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/todas/
  * Node Name: `http://api:8080/api/notificaciones/todas/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos
  * Node Name: `http://api:8080/api/prestamos`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos%3Festado=estado&q=q&desde=desde&hasta=hasta&pageable=
  * Node Name: `http://api:8080/api/prestamos (desde,estado,hasta,pageable,q)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/
  * Node Name: `http://api:8080/api/prestamos/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10
  * Node Name: `http://api:8080/api/prestamos/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10/
  * Node Name: `http://api:8080/api/prestamos/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10/721492380945491583
  * Node Name: `http://api:8080/api/prestamos/10/721492380945491583`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/974254184070843426
  * Node Name: `http://api:8080/api/prestamos/974254184070843426`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/mis%3Fpageable=
  * Node Name: `http://api:8080/api/prestamos/mis (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/mis/
  * Node Name: `http://api:8080/api/prestamos/mis/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/usuario
  * Node Name: `http://api:8080/api/prestamos/usuario`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/usuario/
  * Node Name: `http://api:8080/api/prestamos/usuario/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/usuario/10%3Fpageable=
  * Node Name: `http://api:8080/api/prestamos/usuario/10 (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/usuario/10/
  * Node Name: `http://api:8080/api/prestamos/usuario/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/usuario/751566077171702573
  * Node Name: `http://api:8080/api/prestamos/usuario/751566077171702573`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos
  * Node Name: `http://api:8080/api/qr-codigos`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/
  * Node Name: `http://api:8080/api/qr-codigos/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10
  * Node Name: `http://api:8080/api/qr-codigos/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10/
  * Node Name: `http://api:8080/api/qr-codigos/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10/5150006572334606228
  * Node Name: `http://api:8080/api/qr-codigos/10/5150006572334606228`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/3728339868720634376
  * Node Name: `http://api:8080/api/qr-codigos/3728339868720634376`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/libro
  * Node Name: `http://api:8080/api/qr-codigos/libro`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/libro/
  * Node Name: `http://api:8080/api/qr-codigos/libro/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/libro/10
  * Node Name: `http://api:8080/api/qr-codigos/libro/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/libro/10/
  * Node Name: `http://api:8080/api/qr-codigos/libro/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/libro/560311230533703315
  * Node Name: `http://api:8080/api/qr-codigos/libro/560311230533703315`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes
  * Node Name: `http://api:8080/api/reportes`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/
  * Node Name: `http://api:8080/api/reportes/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/69315571320775203
  * Node Name: `http://api:8080/api/reportes/69315571320775203`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/libros-mas-solicitados
  * Node Name: `http://api:8080/api/reportes/libros-mas-solicitados`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/libros-mas-solicitados/
  * Node Name: `http://api:8080/api/reportes/libros-mas-solicitados/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/multas-cobradas
  * Node Name: `http://api:8080/api/reportes/multas-cobradas`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/multas-cobradas/
  * Node Name: `http://api:8080/api/reportes/multas-cobradas/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/prestamos-diarios
  * Node Name: `http://api:8080/api/reportes/prestamos-diarios`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reportes/prestamos-diarios/
  * Node Name: `http://api:8080/api/reportes/prestamos-diarios/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas
  * Node Name: `http://api:8080/api/reservas`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas%3Fq=q&estado=estado&pageable=
  * Node Name: `http://api:8080/api/reservas (estado,pageable,q)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas/
  * Node Name: `http://api:8080/api/reservas/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas/2195529144151644149
  * Node Name: `http://api:8080/api/reservas/2195529144151644149`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas/mis%3Fpageable=
  * Node Name: `http://api:8080/api/reservas/mis (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas/mis/
  * Node Name: `http://api:8080/api/reservas/mis/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones
  * Node Name: `http://api:8080/api/sanciones`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones%3Factiva=true&pageable=
  * Node Name: `http://api:8080/api/sanciones (activa,pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/
  * Node Name: `http://api:8080/api/sanciones/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/10
  * Node Name: `http://api:8080/api/sanciones/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/10/
  * Node Name: `http://api:8080/api/sanciones/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/10/4114192145097333842
  * Node Name: `http://api:8080/api/sanciones/10/4114192145097333842`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/3538380370529065733
  * Node Name: `http://api:8080/api/sanciones/3538380370529065733`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/mis%3Fpageable=
  * Node Name: `http://api:8080/api/sanciones/mis (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/mis/
  * Node Name: `http://api:8080/api/sanciones/mis/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/usuario
  * Node Name: `http://api:8080/api/sanciones/usuario`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/usuario/
  * Node Name: `http://api:8080/api/sanciones/usuario/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/usuario/10%3Fpageable=
  * Node Name: `http://api:8080/api/sanciones/usuario/10 (pageable)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/usuario/10/
  * Node Name: `http://api:8080/api/sanciones/usuario/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/usuario/7678753476184958090
  * Node Name: `http://api:8080/api/sanciones/usuario/7678753476184958090`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios
  * Node Name: `http://api:8080/api/usuarios`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios%3Fq=q&rol=rol&activo=true&pageable=
  * Node Name: `http://api:8080/api/usuarios (activo,pageable,q,rol)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/
  * Node Name: `http://api:8080/api/usuarios/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/10
  * Node Name: `http://api:8080/api/usuarios/10`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/10/
  * Node Name: `http://api:8080/api/usuarios/10/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/8135756717088529991
  * Node Name: `http://api:8080/api/usuarios/8135756717088529991`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/auth/login
  * Node Name: `http://api:8080/api/auth/login ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `400`
  * Other Info: ``
* URL: http://api:8080/api/auth/login
  * Node Name: `http://api:8080/api/auth/login ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/auth/login
  * Node Name: `http://api:8080/api/auth/login ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `429`
  * Other Info: ``
* URL: http://api:8080/api/auth/login/
  * Node Name: `http://api:8080/api/auth/login/ ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/auth/logout/
  * Node Name: `http://api:8080/api/auth/logout/`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/auth/register
  * Node Name: `http://api:8080/api/auth/register ()({nombre,email,password,telefono,rolId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `400`
  * Other Info: ``
* URL: http://api:8080/api/auth/register
  * Node Name: `http://api:8080/api/auth/register ()({nombre,email,password,telefono,rolId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `429`
  * Other Info: ``
* URL: http://api:8080/api/auth/register/
  * Node Name: `http://api:8080/api/auth/register/ ()({nombre,email,password,telefono,rolId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/autores
  * Node Name: `http://api:8080/api/autores ()({id,nombre,apellido,nacionalidad,activo,createdAt,nombreCompleto})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/autores/
  * Node Name: `http://api:8080/api/autores/ ()({id,nombre,apellido,nacionalidad,activo,createdAt,nombreCompleto})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias
  * Node Name: `http://api:8080/api/categorias ()({id,nombre,descripcion,activo,createdAt})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias/
  * Node Name: `http://api:8080/api/categorias/ ()({id,nombre,descripcion,activo,createdAt})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales
  * Node Name: `http://api:8080/api/editoriales ()({id,nombre,pais,activo,createdAt})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales/
  * Node Name: `http://api:8080/api/editoriales/ ()({id,nombre,pais,activo,createdAt})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros
  * Node Name: `http://api:8080/api/libros ()({titulo,isbn,anioPublicacion,edicion,ejemplaresTotales,ubicacion,descripcion,categoriaId,editorialId,autorIds:[]})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros
  * Node Name: `http://api:8080/api/libros ()({titulo,isbn,anioPublicacion,edicion,ejemplaresTotales,ubicacion,descripcion,categoriaId,editorialId,autorIds:[{}]})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/
  * Node Name: `http://api:8080/api/libros/ ()({titulo,isbn,anioPublicacion,edicion,ejemplaresTotales,ubicacion,descripcion,categoriaId,editorialId,autorIds:[{}]})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/10/pagar
  * Node Name: `http://api:8080/api/multas/10/pagar`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/multas/10/pagar/
  * Node Name: `http://api:8080/api/multas/10/pagar/`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones
  * Node Name: `http://api:8080/api/notificaciones ()({usuarioId,titulo,mensaje,tipo})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/
  * Node Name: `http://api:8080/api/notificaciones/ ()({usuarioId,titulo,mensaje,tipo})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos
  * Node Name: `http://api:8080/api/prestamos ()({usuarioId,inventarioId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/
  * Node Name: `http://api:8080/api/prestamos/ ()({usuarioId,inventarioId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos
  * Node Name: `http://api:8080/api/qr-codigos ()({libroId,codigo,imagenUrl})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/
  * Node Name: `http://api:8080/api/qr-codigos/ ()({libroId,codigo,imagenUrl})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/validar
  * Node Name: `http://api:8080/api/qr-codigos/validar ()({codigo})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/validar/
  * Node Name: `http://api:8080/api/qr-codigos/validar/ ()({codigo})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas
  * Node Name: `http://api:8080/api/reservas ()({usuarioId,libroId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/reservas/
  * Node Name: `http://api:8080/api/reservas/ ()({usuarioId,libroId})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones
  * Node Name: `http://api:8080/api/sanciones ()({usuarioId,tipo,motivo,fechaInicio,fechaFin})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/
  * Node Name: `http://api:8080/api/sanciones/ ()({usuarioId,tipo,motivo,fechaInicio,fechaFin})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios
  * Node Name: `http://api:8080/api/usuarios ()({nombre,email,password,telefono,rolId,activo})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/
  * Node Name: `http://api:8080/api/usuarios/ ()({nombre,email,password,telefono,rolId,activo})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/computeMetadata/v1/
  * Node Name: `http://api:8080/computeMetadata/v1/ ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/latest/meta-data/
  * Node Name: `http://api:8080/latest/meta-data/ ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/metadata/instance
  * Node Name: `http://api:8080/metadata/instance ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/metadata/v1
  * Node Name: `http://api:8080/metadata/v1 ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/opc/v1/instance/
  * Node Name: `http://api:8080/opc/v1/instance/ ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/opc/v2/instance/
  * Node Name: `http://api:8080/opc/v2/instance/ ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/openstack/latest/meta_data.json
  * Node Name: `http://api:8080/openstack/latest/meta_data.json ()({email,password})`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `404`
  * Other Info: ``
* URL: http://api:8080/api/autores/10
  * Node Name: `http://api:8080/api/autores/10 ()({id,nombre,apellido,nacionalidad,activo,createdAt,nombreCompleto})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/autores/10/
  * Node Name: `http://api:8080/api/autores/10/ ()({id,nombre,apellido,nacionalidad,activo,createdAt,nombreCompleto})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias/10
  * Node Name: `http://api:8080/api/categorias/10 ()({id,nombre,descripcion,activo,createdAt})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/categorias/10/
  * Node Name: `http://api:8080/api/categorias/10/ ()({id,nombre,descripcion,activo,createdAt})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/configuracion/10
  * Node Name: `http://api:8080/api/configuracion/10 ()({valor})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/configuracion/10/
  * Node Name: `http://api:8080/api/configuracion/10/ ()({valor})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales/10
  * Node Name: `http://api:8080/api/editoriales/10 ()({id,nombre,pais,activo,createdAt})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/editoriales/10/
  * Node Name: `http://api:8080/api/editoriales/10/ ()({id,nombre,pais,activo,createdAt})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/10
  * Node Name: `http://api:8080/api/libros/10 ()({titulo,isbn,anioPublicacion,edicion,ejemplaresTotales,ubicacion,descripcion,categoriaId,editorialId,autorIds:[]})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/10
  * Node Name: `http://api:8080/api/libros/10 ()({titulo,isbn,anioPublicacion,edicion,ejemplaresTotales,ubicacion,descripcion,categoriaId,editorialId,autorIds:[{}]})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/libros/10/
  * Node Name: `http://api:8080/api/libros/10/ ()({titulo,isbn,anioPublicacion,edicion,ejemplaresTotales,ubicacion,descripcion,categoriaId,editorialId,autorIds:[{}]})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/10/leida
  * Node Name: `http://api:8080/api/notificaciones/10/leida`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/10/leida/
  * Node Name: `http://api:8080/api/notificaciones/10/leida/`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/leer-todas
  * Node Name: `http://api:8080/api/notificaciones/leer-todas`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/leer-todas/
  * Node Name: `http://api:8080/api/notificaciones/leer-todas/`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10/devolver
  * Node Name: `http://api:8080/api/prestamos/10/devolver`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10/devolver/
  * Node Name: `http://api:8080/api/prestamos/10/devolver/`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10/renovar
  * Node Name: `http://api:8080/api/prestamos/10/renovar`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10/renovar/
  * Node Name: `http://api:8080/api/prestamos/10/renovar/`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10/activo%3Factivo=true
  * Node Name: `http://api:8080/api/qr-codigos/10/activo (activo)`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10/activo/
  * Node Name: `http://api:8080/api/qr-codigos/10/activo/`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10/regenerar
  * Node Name: `http://api:8080/api/qr-codigos/10/regenerar`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10/regenerar/
  * Node Name: `http://api:8080/api/qr-codigos/10/regenerar/`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/10/levantar
  * Node Name: `http://api:8080/api/sanciones/10/levantar`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/sanciones/10/levantar/
  * Node Name: `http://api:8080/api/sanciones/10/levantar/`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/10
  * Node Name: `http://api:8080/api/usuarios/10 ()({nombre,email,password,telefono,rolId,activo})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``
* URL: http://api:8080/api/usuarios/10/
  * Node Name: `http://api:8080/api/usuarios/10/ ()({nombre,email,password,telefono,rolId,activo})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `401`
  * Other Info: ``


Instances: 219

### Solution



### Reference



#### CWE Id: [ 388 ](https://cwe.mitre.org/data/definitions/388.html)


#### WASC Id: 20

#### Source ID: 4

### [ Authentication Request Identified ](https://www.zaproxy.org/docs/alerts/10111/)



##### Informational (Low)

### Description

The given request has been identified as an authentication request. The 'Other Info' field contains a set of key=value lines which identify any relevant fields. If the request is in a context which has an Authentication Method set to "Auto-Detect" then this rule will change the authentication to match the request identified.

* URL: http://api:8080/api/usuarios
  * Node Name: `http://api:8080/api/usuarios ()({nombre,email,password,telefono,rolId,activo})`
  * Method: `POST`
  * Parameter: `email`
  * Attack: ``
  * Evidence: `password`
  * Other Info: `userParam=email
userValue=zaproxy@example.com
passwordParam=password`
* URL: http://api:8080/api/auth/login
  * Node Name: `http://api:8080/api/auth/login ()({email,password})`
  * Method: `POST`
  * Parameter: `email`
  * Attack: ``
  * Evidence: `password`
  * Other Info: `userParam=email
userValue=zaproxy@example.com
passwordParam=password`


Instances: 2

### Solution

This is an informational alert rather than a vulnerability and so there is nothing to fix.

### Reference


* [ https://www.zaproxy.org/docs/desktop/addons/authentication-helper/auth-req-id/ ](https://www.zaproxy.org/docs/desktop/addons/authentication-helper/auth-req-id/)



#### Source ID: 3

### [ Non-Storable Content ](https://www.zaproxy.org/docs/alerts/10049/)



##### Informational (Medium)

### Description

The response contents are not storable by caching components such as proxy servers. If the response does not contain sensitive, personal or user-specific information, it may benefit from being stored and cached, to improve performance.

* URL: http://api:8080/api-docs
  * Node Name: `http://api:8080/api-docs`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `no-store`
  * Other Info: ``
* URL: http://api:8080/api/notificaciones/10/leida
  * Node Name: `http://api:8080/api/notificaciones/10/leida`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `PUT `
  * Other Info: ``
* URL: http://api:8080/api/prestamos/10/devolver
  * Node Name: `http://api:8080/api/prestamos/10/devolver`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `PUT `
  * Other Info: ``
* URL: http://api:8080/api/qr-codigos/10/regenerar
  * Node Name: `http://api:8080/api/qr-codigos/10/regenerar`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `PUT `
  * Other Info: ``
* URL: http://api:8080/api/usuarios/10
  * Node Name: `http://api:8080/api/usuarios/10 ()({nombre,email,password,telefono,rolId,activo})`
  * Method: `PUT`
  * Parameter: ``
  * Attack: ``
  * Evidence: `PUT `
  * Other Info: ``

Instances: Systemic


### Solution

The content may be marked as storable by ensuring that the following conditions are satisfied:
The request method must be understood by the cache and defined as being cacheable ("GET", "HEAD", and "POST" are currently defined as cacheable)
The response status code must be understood by the cache (one of the 1XX, 2XX, 3XX, 4XX, or 5XX response classes are generally understood)
The "no-store" cache directive must not appear in the request or response header fields
For caching by "shared" caches such as "proxy" caches, the "private" response directive must not appear in the response
For caching by "shared" caches such as "proxy" caches, the "Authorization" header field must not appear in the request, unless the response explicitly allows it (using one of the "must-revalidate", "public", or "s-maxage" Cache-Control response directives)
In addition to the conditions above, at least one of the following conditions must also be satisfied by the response:
It must contain an "Expires" header field
It must contain a "max-age" response directive
For "shared" caches such as "proxy" caches, it must contain a "s-maxage" response directive
It must contain a "Cache Control Extension" that allows it to be cached
It must have a status code that is defined as cacheable by default (200, 203, 204, 206, 300, 301, 404, 405, 410, 414, 501).

### Reference


* [ https://datatracker.ietf.org/doc/html/rfc7234 ](https://datatracker.ietf.org/doc/html/rfc7234)
* [ https://datatracker.ietf.org/doc/html/rfc7231 ](https://datatracker.ietf.org/doc/html/rfc7231)
* [ https://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html ](https://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html)


#### CWE Id: [ 524 ](https://cwe.mitre.org/data/definitions/524.html)


#### WASC Id: 13

#### Source ID: 3


