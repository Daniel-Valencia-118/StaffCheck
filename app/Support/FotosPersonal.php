<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FotosPersonal
{
    public const CARPETA = 'personas/fotos';

    public const DISCO = 'local';

    public const DISCO_ANTIGUO = 'public';

    public const LADO_MAXIMO = 1024;

    private const CALIDAD = 85;

    private const TIPOS = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];

    public static function procesable(): bool
    {
        return function_exists('imagecreatefromstring') && function_exists('imagejpeg') && function_exists('imagecopyresampled');
    }

    public static function legible(UploadedFile $foto): bool
    {
        if (! self::procesable()) {
            return true;
        }

        $imagen = @imagecreatefromstring((string) file_get_contents($foto->getRealPath()));
        if ($imagen === false) {
            return false;
        }

        return true;
    }

    public static function guardar(UploadedFile $foto): string
    {
        $nombre = Str::random(40);
        $contenido = self::procesable() ? self::reprocesar($foto) : null;

        if ($contenido !== null) {
            $ruta = self::CARPETA."/{$nombre}.jpg";
            Storage::disk(self::DISCO)->put($ruta, $contenido);

            return $ruta;
        }

        $extension = array_key_exists($foto->extension(), self::TIPOS) ? $foto->extension() : 'jpg';

        return $foto->storeAs(self::CARPETA, "{$nombre}.{$extension}", self::DISCO);
    }

    public static function url(int $idPersona, string $valor): string
    {
        return route('personal.foto', ['id' => $idPersona, 'v' => substr(sha1($valor), 0, 10)]);
    }

    public static function respuesta(?string $valor, string $cache = 'private, max-age=86400'): ?Response
    {
        $archivo = self::ubicar($valor);
        if (! $archivo) {
            return null;
        }

        [$disco, $ruta] = $archivo;

        return Storage::disk($disco)->response($ruta, null, [
            'Content-Type' => self::tipo($ruta),
            'Cache-Control' => $cache,
        ]);
    }

    public static function dataUri(?string $valor): ?string
    {
        $archivo = self::ubicar($valor);
        if (! $archivo) {
            return null;
        }

        [$disco, $ruta] = $archivo;

        return 'data:'.self::tipo($ruta).';base64,'.base64_encode(Storage::disk($disco)->get($ruta));
    }

    public static function dataUriRecortada(?string $valor, int $ancho, int $alto): ?string
    {
        $archivo = self::ubicar($valor);
        if (! $archivo || ! self::procesable()) {
            return self::dataUri($valor);
        }

        [$disco, $ruta] = $archivo;
        $imagen = @imagecreatefromstring(Storage::disk($disco)->get($ruta));
        if ($imagen === false) {
            return self::dataUri($valor);
        }

        $origenAncho = imagesx($imagen);
        $origenAlto = imagesy($imagen);
        $escala = max($ancho / $origenAncho, $alto / $origenAlto);
        $recorteAncho = (int) round($ancho / $escala);
        $recorteAlto = (int) round($alto / $escala);

        $lienzo = imagecreatetruecolor($ancho, $alto);
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));
        imagecopyresampled(
            $lienzo, $imagen, 0, 0,
            intdiv($origenAncho - $recorteAncho, 2), intdiv($origenAlto - $recorteAlto, 2),
            $ancho, $alto, $recorteAncho, $recorteAlto,
        );

        ob_start();
        imagejpeg($lienzo, null, self::CALIDAD);

        return 'data:image/jpeg;base64,'.base64_encode((string) ob_get_clean());
    }

    public static function borrar(?string $valor): void
    {
        $nombre = self::nombre($valor);
        if (! $nombre) {
            return;
        }

        foreach ([self::DISCO, self::DISCO_ANTIGUO] as $disco) {
            Storage::disk($disco)->delete(self::CARPETA.'/'.$nombre);
        }
    }

    public static function resumen(): array
    {
        $contar = function (string $disco) {
            $archivos = collect(Storage::disk($disco)->files(self::CARPETA));

            return ['fotos' => $archivos->count(), 'bytes' => $archivos->sum(fn (string $ruta) => Storage::disk($disco)->size($ruta))];
        };

        return ['privadas' => $contar(self::DISCO), 'publicas' => $contar(self::DISCO_ANTIGUO)];
    }

    public static function protegerAntiguas(): array
    {
        $antiguo = Storage::disk(self::DISCO_ANTIGUO);
        $privado = Storage::disk(self::DISCO);
        $movidas = 0;
        $fallidas = 0;

        foreach ($antiguo->files(self::CARPETA) as $ruta) {
            if (! self::nombre($ruta)) {
                continue;
            }

            $tamano = $antiguo->size($ruta);

            if (! $privado->exists($ruta) || $privado->size($ruta) !== $tamano) {
                $privado->put($ruta, $antiguo->get($ruta));
            }

            if ($privado->exists($ruta) && $privado->size($ruta) === $tamano) {
                $antiguo->delete($ruta);
                $movidas++;
            } else {
                $fallidas++;
            }
        }

        return ['movidas' => $movidas, 'fallidas' => $fallidas];
    }

    private static function ubicar(?string $valor): ?array
    {
        $nombre = self::nombre($valor);
        if (! $nombre) {
            return null;
        }

        foreach ([self::DISCO, self::DISCO_ANTIGUO] as $disco) {
            $ruta = self::CARPETA.'/'.$nombre;
            if (Storage::disk($disco)->exists($ruta)) {
                return [$disco, $ruta];
            }
        }

        return null;
    }

    private static function nombre(?string $valor): ?string
    {
        if (! $valor || ! preg_match('#(?:^|/)personas/fotos/([A-Za-z0-9_-]+\.(?:jpe?g|png))$#i', $valor, $coincidencia)) {
            return null;
        }

        return $coincidencia[1];
    }

    private static function tipo(string $ruta): string
    {
        return self::TIPOS[strtolower(pathinfo($ruta, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
    }

    private static function reprocesar(UploadedFile $foto): ?string
    {
        $imagen = @imagecreatefromstring((string) file_get_contents($foto->getRealPath()));
        if ($imagen === false) {
            return null;
        }

        $imagen = self::orientar($imagen, $foto);
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $escala = min(1, self::LADO_MAXIMO / max($ancho, $alto));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));
        imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        ob_start();
        imagejpeg($lienzo, null, self::CALIDAD);

        return ob_get_clean() ?: null;
    }

    private static function orientar($imagen, UploadedFile $foto)
    {
        if (! function_exists('exif_read_data') || $foto->getMimeType() !== 'image/jpeg') {
            return $imagen;
        }

        $exif = @exif_read_data($foto->getRealPath());
        $grados = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($grados === 0) {
            return $imagen;
        }

        $rotada = imagerotate($imagen, $grados, 0);
        if ($rotada === false) {
            return $imagen;
        }

        return $rotada;
    }
}
