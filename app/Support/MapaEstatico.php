<?php

namespace App\Support;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MapaEstatico
{
    public const ZOOM = 18;

    public const ANCHO = 1000;

    public const ALTO = 300;

    private const TAMANO_TILE = 256;

    private const URL_TILE = 'https://tile.openstreetmap.org/%d/%d/%d.png';

    private const SEGUNDOS_ESPERA = 5;

    public const CARPETA_CACHE = 'mapas';

    public static function disponible(): bool
    {
        return function_exists('imagecreatetruecolor') && function_exists('imagecreatefromstring');
    }

    public static function dataUri(?float $latitud, ?float $longitud): ?string
    {
        if ($latitud === null || $longitud === null || ! self::disponible()) {
            return null;
        }

        $disco = Storage::disk(FotosPersonal::DISCO);
        $ruta = self::CARPETA_CACHE.'/'.sha1(implode('|', [round($latitud, 6), round($longitud, 6), self::ZOOM, self::ANCHO, self::ALTO])).'.jpg';

        $imagen = $disco->exists($ruta) ? $disco->get($ruta) : null;

        if (! $imagen) {
            try {
                $imagen = self::generar($latitud, $longitud);
            } catch (Throwable $e) {
                report($e);

                return null;
            }

            if ($imagen) {
                $disco->put($ruta, $imagen);
            }
        }

        return $imagen ? 'data:image/jpeg;base64,'.base64_encode($imagen) : null;
    }

    private static function generar(float $latitud, float $longitud): ?string
    {
        [$centroX, $centroY] = self::aPixeles($latitud, $longitud, self::ZOOM);
        $izquierda = (int) floor($centroX - self::ANCHO / 2);
        $arriba = (int) floor($centroY - self::ALTO / 2);

        $tiles = [];
        $limite = 2 ** self::ZOOM;
        for ($tx = intdiv($izquierda, self::TAMANO_TILE); $tx <= intdiv($izquierda + self::ANCHO - 1, self::TAMANO_TILE); $tx++) {
            for ($ty = intdiv($arriba, self::TAMANO_TILE); $ty <= intdiv($arriba + self::ALTO - 1, self::TAMANO_TILE); $ty++) {
                if ($ty >= 0 && $ty < $limite) {
                    $tiles["{$tx}_{$ty}"] = [$tx, $ty];
                }
            }
        }

        $respuestas = Http::pool(fn (Pool $pool) => array_map(
            fn (string $clave, array $tile) => $pool->as($clave)
                ->withHeaders(['User-Agent' => config('app.name').'/1.0 (+'.config('app.url').')'])
                ->timeout(self::SEGUNDOS_ESPERA)
                ->get(sprintf(self::URL_TILE, self::ZOOM, (($tile[0] % $limite) + $limite) % $limite, $tile[1])),
            array_keys($tiles),
            $tiles,
        ));

        $lienzo = imagecreatetruecolor(self::ANCHO, self::ALTO);
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 235, 235, 235));
        $dibujados = 0;

        foreach ($tiles as $clave => [$tx, $ty]) {
            $respuesta = $respuestas[$clave] ?? null;
            if (! $respuesta instanceof Response || ! $respuesta->successful()) {
                continue;
            }

            $imagen = @imagecreatefromstring($respuesta->body());
            if ($imagen === false) {
                continue;
            }

            imagecopy($lienzo, $imagen, $tx * self::TAMANO_TILE - $izquierda, $ty * self::TAMANO_TILE - $arriba, 0, 0, self::TAMANO_TILE, self::TAMANO_TILE);
            $dibujados++;
        }

        if ($dibujados === 0) {

            return null;
        }

        self::dibujarMarcador($lienzo, (int) round($centroX - $izquierda), (int) round($centroY - $arriba));
        self::dibujarAtribucion($lienzo);

        ob_start();
        imagejpeg($lienzo, null, 82);

        return ob_get_clean() ?: null;
    }

    private static function aPixeles(float $latitud, float $longitud, int $zoom): array
    {
        $escala = self::TAMANO_TILE * (2 ** $zoom);
        $latitud = max(min($latitud, 85.0511), -85.0511);
        $radianes = deg2rad($latitud);

        return [
            ($longitud + 180) / 360 * $escala,
            (1 - log(tan($radianes) + 1 / cos($radianes)) / M_PI) / 2 * $escala,
        ];
    }

    private static function dibujarMarcador($lienzo, int $x, int $y): void
    {
        $sombra = @imagecreatefrompng(resource_path('images/mapa/marker-shadow.png'));
        $icono = @imagecreatefrompng(resource_path('images/mapa/marker-icon-2x.png'));

        if ($sombra === false || $icono === false) {
            imagefilledellipse($lienzo, $x, $y, 16, 16, imagecolorallocate($lienzo, 37, 99, 235));

            return;
        }

        imagealphablending($lienzo, true);
        imagecopy($lienzo, $sombra, $x - 12, $y - 41, 0, 0, imagesx($sombra), imagesy($sombra));
        imagecopyresampled($lienzo, $icono, $x - 12, $y - 41, 0, 0, 25, 41, imagesx($icono), imagesy($icono));
    }

    private static function dibujarAtribucion($lienzo): void
    {
        $texto = '(c) OpenStreetMap';
        $ancho = imagefontwidth(2) * strlen($texto) + 8;
        imagefilledrectangle($lienzo, self::ANCHO - $ancho, self::ALTO - 16, self::ANCHO, self::ALTO, imagecolorallocate($lienzo, 255, 255, 255));
        imagestring($lienzo, 2, self::ANCHO - $ancho + 4, self::ALTO - 15, $texto, imagecolorallocate($lienzo, 60, 60, 60));
    }
}
