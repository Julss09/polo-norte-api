<?php

namespace App\Adapters;

use Illuminate\Support\Facades\Http;

class MeteoAdapter
{
    public function obtenerPronostico($zona, $fecha)
    {
        try {
            $endpoint = config('services.meteo.endpoint');

            if (!$endpoint) {
                return null;
            }

            $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mt="http://polonorte.beatlech/meteo">
    <soapenv:Body>
        <mt:ObtenerPronosticoRequest>
            <mt:zona>{$zona}</mt:zona>
            <mt:fecha>{$fecha}</mt:fecha>
        </mt:ObtenerPronosticoRequest>
    </soapenv:Body>
</soapenv:Envelope>
XML;

            $respuesta = Http::timeout(3)
                ->connectTimeout(3)
                ->withHeaders([
                    'SOAPAction' => 'ObtenerPronostico'
                ])
                ->withBody($xml, 'text/xml; charset=utf-8')
                ->post($endpoint);

            if (!$respuesta->successful()) {
                return null;
            }

            $documento = simplexml_load_string(
                $respuesta->body(),
                'SimpleXMLElement',
                LIBXML_NONET
            );

            if (!$documento) {
                return null;
            }

            if ($documento->xpath('//*[local-name()="Fault"]')) {
                return null;
            }

            $resultados = $documento->xpath(
                '//*[local-name()="pronostico"]'
            );

            $pronostico = (string) ($resultados[0] ?? '');

            return in_array($pronostico, [
                'DESPEJADO',
                'NEVADA_LEVE',
                'VENTISCA'
            ]) ? $pronostico : null;

        } catch (\Throwable $e) {
            return null;
        }
    }
}
