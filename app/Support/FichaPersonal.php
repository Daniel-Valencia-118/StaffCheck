<?php

namespace App\Support;

use App\Models\Persona;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class FichaPersonal
{
    public const CAMPOS = [
        'paterno', 'materno', 'nombres', 'ci', 'ci_expedicion', 'sexo',
        'fecha_nacimiento', 'lugar_nacimiento_provincia', 'lugar_nacimiento_ciudad',
        'estado_civil', 'numero_hijos', 'email', 'telefono', 'celular',
        'direccion_actual', 'fecha_ingreso_fundacion', 'cargo_actual',
        'url_croquis', 'latitude', 'longitude', 'oficina_actual',
    ];

    public const RELACIONES = [
        'estudios' => ['relacion' => 'estudios', 'clave' => 'tipo', 'campos' => ['tipo', 'titulo_obtenido', 'institucion', 'anio']],
        'experiencias' => ['relacion' => 'experienciasLaborales', 'clave' => 'institucion', 'campos' => ['institucion', 'cargo', 'fecha_inicio', 'fecha_fin']],
        'referencias' => ['relacion' => 'referenciasLaborales', 'clave' => 'nombre_referente', 'campos' => ['nombre_referente', 'institucion', 'telefono_celular']],
        'contactos' => ['relacion' => 'contactos', 'clave' => 'nombre', 'campos' => ['nombre', 'paterno', 'materno', 'parentesco_relacion', 'edad', 'telefono_celular', 'es_familiar']],
    ];

    public const MENSAJE_DUPLICADO = 'No pudimos registrar estos datos. Si ya se registró antes, comuníquese con Recursos Humanos.';

    public static function reglas(int $fotoMaxKb, ?Persona $persona = null): array
    {
        return [
            'paterno' => 'required|string|max:100',
            'materno' => 'required|string|max:100',
            'nombres' => 'required|string|max:150',
            'ci' => ['required', 'string', 'max:15', Rule::unique('personas', 'ci')->ignore($persona?->id)],
            'ci_expedicion' => 'required|string|max:10',
            'sexo' => 'required|string|max:15',
            'fecha_nacimiento' => 'required|date|before:today',
            'lugar_nacimiento_provincia' => 'required|string|max:100',
            'lugar_nacimiento_ciudad' => 'required|string|max:100',
            'estado_civil' => 'required|string|max:20',
            'numero_hijos' => 'nullable|integer|min:0',
            'email' => ['required', 'email', 'max:150', Rule::unique('personas', 'email')->ignore($persona?->id)],
            'telefono' => 'nullable|string|max:20',
            'celular' => 'required|string|max:20',
            'direccion_actual' => 'required|string|max:255',
            'fecha_ingreso_fundacion' => 'required|date',
            'cargo_actual' => 'required|string|max:100',
            'oficina_actual' => 'required|string|max:100',
            'foto' => [$persona ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:'.$fotoMaxKb, function (string $atributo, mixed $valor, Closure $fallar) {
                if ($valor instanceof UploadedFile && ! FotosPersonal::legible($valor)) {
                    $fallar('La imagen está dañada o no se pudo leer. Pruebe con otra foto.');
                }
            }],
            'url_croquis' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

            'estudios' => 'nullable|array',
            'estudios.*.tipo' => 'required_with:estudios.*.titulo_obtenido,estudios.*.institucion|string|max:50',
            'estudios.*.titulo_obtenido' => 'required_with:estudios.*.tipo,estudios.*.institucion|string|max:200',
            'estudios.*.institucion' => 'required_with:estudios.*.tipo,estudios.*.titulo_obtenido|string|max:200',
            'estudios.*.anio' => 'required_with:estudios.*.tipo,estudios.*.titulo_obtenido|integer|min:1900|max:'.(intval(date('Y')) + 5),

            'experiencias' => 'nullable|array',
            'experiencias.*.institucion' => 'required_with:experiencias.*.cargo,experiencias.*.fecha_inicio|string|max:200',
            'experiencias.*.cargo' => 'required_with:experiencias.*.institucion,experiencias.*.fecha_inicio|string|max:150',
            'experiencias.*.fecha_inicio' => 'required_with:experiencias.*.institucion,experiencias.*.cargo|date',
            'experiencias.*.fecha_fin' => 'nullable|date|after_or_equal:experiencias.*.fecha_inicio',

            'referencias' => 'nullable|array',
            'referencias.*.nombre_referente' => 'required_with:referencias.*.institucion,referencias.*.telefono_celular|string|max:200',
            'referencias.*.institucion' => 'required_with:referencias.*.nombre_referente,referencias.*.telefono_celular|string|max:200',
            'referencias.*.telefono_celular' => 'required_with:referencias.*.nombre_referente,referencias.*.institucion|string|max:20',

            'contactos' => 'nullable|array',
            'contactos.*.nombre' => 'required_with:contactos.*.paterno,contactos.*.parentesco_relacion|string|max:100',
            'contactos.*.paterno' => 'required_with:contactos.*.nombre,contactos.*.parentesco_relacion|string|max:100',
            'contactos.*.materno' => 'required_with:contactos.*.nombre,contactos.*.parentesco_relacion|string|max:100',
            'contactos.*.parentesco_relacion' => 'required_with:contactos.*.nombre,contactos.*.paterno|string|max:50',
            'contactos.*.edad' => 'nullable|integer|min:0',
            'contactos.*.telefono_celular' => 'required_with:contactos.*.nombre,contactos.*.parentesco_relacion|string|max:20',
            'contactos.*.es_familiar' => 'required_with:contactos.*.nombre,contactos.*.parentesco_relacion|boolean',
        ];
    }

    public static function mensajes(int $fotoMaxKb, bool $publico = false): array
    {
        return [
            'ci.unique' => $publico ? self::MENSAJE_DUPLICADO : 'Ya existe una ficha con este número de CI.',
            'email.unique' => $publico ? self::MENSAJE_DUPLICADO : 'Este correo electrónico ya está registrado.',
            'paterno' => 'El apellido paterno es obligatorio.',
            'materno' => 'El apellido materno es obligatorio.',
            'nombres' => 'El nombre es obligatorio.',
            'ci' => 'El número de CI es obligatorio y debe ser único.',
            'ci_expedicion' => 'La expedición del CI es obligatoria.',
            'sexo' => 'El sexo es obligatorio.',
            'fecha_nacimiento' => 'La fecha de nacimiento es obligatoria.',
            'lugar_nacimiento_provincia' => 'La provincia de nacimiento es obligatoria.',
            'lugar_nacimiento_ciudad' => 'La ciudad de nacimiento es obligatoria.',
            'estado_civil' => 'El estado civil es obligatorio.',
            'numero_hijos' => 'El número de hijos debe ser un número entero no negativo.',
            'email' => 'El correo electrónico debe ser válido.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'telefono' => 'El teléfono debe ser válido.',
            'celular' => 'El celular debe ser válido.',
            'direccion_actual' => 'La dirección actual es obligatoria.',
            'fecha_ingreso_fundacion' => 'La fecha de ingreso a la fundación es obligatoria.',
            'cargo_actual' => 'El cargo actual es obligatorio.',
            'oficina_actual' => 'La oficina actual es obligatoria.',
            'latitude.required' => 'La dirección es obligatoria.',

            'foto.required' => 'La foto de perfil es obligatoria.',
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.mimes' => 'La foto debe estar en formato JPEG, PNG o JPG.',
            'foto.max' => 'La foto de perfil no debe pesar más de '.round($fotoMaxKb / 1024, 1).' MB.',

            'estudios.*.tipo' => 'El tipo de estudio es obligatorio.',
            'estudios.*.titulo_obtenido' => 'El título obtenido es obligatorio.',
            'estudios.*.institucion' => 'La institución es obligatoria.',

            'experiencias.*.institucion' => 'La institución de la experiencia laboral es obligatoria.',
            'experiencias.*.cargo' => 'El cargo de la experiencia laboral es obligatorio.',
            'experiencias.*.fecha_inicio' => 'La fecha de inicio de la experiencia laboral es obligatoria.',

            'referencias.*.nombre_referente' => 'El nombre del referente laboral es obligatorio.',
            'referencias.*.institucion' => 'La institución del referente laboral es obligatoria.',
            'referencias.*.telefono_celular' => 'El teléfono o celular del referente laboral es obligatorio.',

            'contactos.*.nombre' => 'El nombre del contacto es obligatorio.',
            'contactos.*.paterno' => 'El apellido paterno del contacto es obligatorio.',
            'contactos.*.materno' => 'El apellido materno del contacto es obligatorio.',
            'contactos.*.parentesco_relacion' => 'El parentesco o relación del contacto es obligatorio.',
            'contactos.*.telefono_celular' => 'El teléfono o celular del contacto es obligatorio.',
            'contactos.*.es_familiar' => 'Debe indicar si el contacto es familiar o no.',
        ];
    }

    public static function guardarRelaciones(Persona $persona, array $datos, bool $reemplazar = false): void
    {
        foreach (self::RELACIONES as $seccion => $definicion) {
            $relacion = $persona->{$definicion['relacion']}();

            if ($reemplazar) {
                $relacion->delete();
            }

            foreach ($datos[$seccion] ?? [] as $fila) {
                if (blank($fila[$definicion['clave']] ?? null)) {
                    continue;
                }

                $valores = [];
                foreach ($definicion['campos'] as $campo) {
                    $valores[$campo] = $fila[$campo] ?? null;
                }

                $relacion->create($valores);
            }
        }
    }

    public static function actualizar(Persona $persona, array $datos, ?UploadedFile $foto, ?Closure $alGuardar = null): array
    {
        $fotoAnterior = $persona->getRawOriginal('url_foto');
        $rutaFoto = $foto ? FotosPersonal::guardar($foto) : null;

        try {
            $cambios = DB::transaction(function () use ($persona, $datos, $rutaFoto, $alGuardar) {
                $persona->fill(array_intersect_key($datos, array_flip(self::CAMPOS)));
                $persona->numero_hijos ??= 0;

                if ($rutaFoto) {
                    $persona->url_foto = $rutaFoto;
                }

                $persona->save();
                $cambios = array_keys($persona->getChanges());

                self::guardarRelaciones($persona, array_intersect_key($datos, self::RELACIONES), reemplazar: true);

                if ($alGuardar) {
                    $alGuardar($persona);
                }

                return $cambios;
            });
        } catch (Throwable $e) {
            FotosPersonal::borrar($rutaFoto);

            throw $e;
        }

        if ($rutaFoto) {
            FotosPersonal::borrar($fotoAnterior);
        }

        return array_values(array_diff($cambios, ['updated_at']));
    }

    public static function paraFormulario(Persona $persona): array
    {
        $fecha = fn ($valor) => $valor?->format('Y-m-d');
        $titulo = fn (?string $valor) => $valor ? mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8') : '';

        return [
            ...$persona->only(self::CAMPOS),
            'sexo' => mb_strtoupper((string) $persona->sexo, 'UTF-8'),
            'estado_civil' => mb_strtoupper((string) $persona->estado_civil, 'UTF-8'),
            'oficina_actual' => $titulo($persona->oficina_actual),
            'fecha_nacimiento' => $fecha($persona->fecha_nacimiento),
            'fecha_ingreso_fundacion' => $fecha($persona->fecha_ingreso_fundacion),
            'foto' => null,
            'estudios' => $persona->estudios->map(fn ($e) => [
                ...$e->only(self::RELACIONES['estudios']['campos']),
                'tipo' => $titulo($e->tipo),
            ])->all(),
            'experiencias' => $persona->experienciasLaborales->map(fn ($e) => [
                ...$e->only(self::RELACIONES['experiencias']['campos']),
                'fecha_inicio' => $fecha($e->fecha_inicio),
                'fecha_fin' => $fecha($e->fecha_fin),
            ])->all(),
            'referencias' => $persona->referenciasLaborales->map(fn ($r) => $r->only(self::RELACIONES['referencias']['campos']))->all(),
            'contactos' => $persona->contactos->map(fn ($c) => $c->only(self::RELACIONES['contactos']['campos']))->all(),
        ];
    }
}
