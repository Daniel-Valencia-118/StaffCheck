@php
    $fecha = fn ($valor) => $valor ? \Carbon\Carbon::parse($valor)->format('d/m/Y') : '-';
    $nombre = fn ($contacto) => trim("{$contacto->nombre} {$contacto->paterno} {$contacto->materno}");
    $familiares = $persona->contactos->where('es_familiar', true)->values();
    $personales = $persona->contactos->where('es_familiar', false)->values();
    $fila = fn ($loop) => $loop->last ? '' : 'con-linea';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha_Personal_{{ $persona->ci }}</title>
    <style>
        @page { margin: 72pt; }
        body { font-family: Helvetica, sans-serif; font-size: 8.5pt; line-height: 1.1; color: #000; margin: 0; }

        .numero-pagina { position: fixed; top: -36pt; right: 0; font-size: 10pt; line-height: 1; }
        .numero-pagina:after { content: counter(page); }

        .encabezado { width: 100%; border-collapse: collapse; margin-bottom: 12pt; }
        .encabezado td { padding: 0; vertical-align: middle; font-weight: bold; }
        .institucion { width: 40%; font-size: 11pt; }
        .titulo { width: 60%; font-size: 12pt; text-align: right; letter-spacing: 0.5pt; }

        .seccion { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 8pt; page-break-inside: avoid; }
        .seccion > tbody > tr > td { padding: 0; vertical-align: top; border-left: 1pt solid #555; border-right: 1pt solid #555; border-bottom: 1pt solid #555; }
        .seccion > tbody > tr > td.seccion-titulo {
            border-top: 1pt solid #555; background: #e2e2e2; font-weight: bold; font-size: 9pt; text-align: center; text-transform: uppercase; padding: 3pt;
        }
        .seccion > tbody > tr > td.foto-celda { border: none; width: 105pt; padding-left: 4pt; }

        .fila, .tabla { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
        .fila.con-linea > tbody > tr > td, .tabla tr.con-linea > td, .tabla tr.con-linea > th { border-bottom: 1pt solid #555; }

        .fila td, .tabla td, .tabla th { padding: 3pt; font-size: 9pt; vertical-align: top; text-align: left; }
        .fila td.linea, .tabla td.linea, .tabla th.linea { border-right: 1pt solid #555; }
        .tabla th { background: #f2f2f2; font-weight: bold; text-align: center; }
        .fila td.etiqueta { background: #fafafa; font-weight: bold; text-align: center; }
        .fila td.etiqueta-izq { background: #fafafa; font-weight: bold; text-transform: uppercase; }
        .mayus { text-transform: uppercase; }
        .tabla td.centro { text-align: center; }
        .vacio { font-style: italic; color: #666; text-align: center; padding: 5pt; font-size: 8.5pt; }

        .foto { border-collapse: separate; border-spacing: 0; }
        .foto td { width: 103pt; height: 113pt; border: 1pt solid #555; background: #fafafa; text-align: center; vertical-align: middle; padding: 0; }
        .foto img { width: 103pt; height: 113pt; }
        .foto-vacia { font-size: 8pt; font-style: italic; color: #777; }

        .croquis img { width: 100%; height: 139pt; }

        .firmas { width: 100%; margin-top: 50pt; border-collapse: collapse; page-break-inside: avoid; }
        .firmas td { width: 45%; padding: 0; text-align: center; font-weight: bold; font-size: 9pt; vertical-align: top; }
        .firmas td.espacio { width: 10%; }
        .linea-firma { border-top: 1pt solid #000; width: 75%; margin: 0 auto 4pt auto; height: 0; }
    </style>
</head>
<body>
    <div class="numero-pagina"></div>

    <table class="encabezado">
        <tr>
            <td class="institucion">Fundación ALALAY</td>
            <td class="titulo">FICHA PERSONAL</td>
        </tr>
    </table>

    <table class="seccion">
        <tr><td class="seccion-titulo" colspan="2">Información General</td></tr>
        <tr>
            <td>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 28%;">Apellido Paterno</td>
                    <td class="mayus">{{ $persona->paterno }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 28%;">Apellido Materno</td>
                    <td class="mayus">{{ $persona->materno }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 28%;">Nombres</td>
                    <td class="mayus">{{ $persona->nombres }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 20%;">C.I.</td>
                    <td class="mayus linea" style="width: 30%;">{{ $persona->ci }} {{ $persona->ci_expedicion }}</td>
                    <td class="etiqueta linea" style="width: 20%;">Sexo</td>
                    <td class="mayus">{{ $persona->sexo }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 20%;">Teléfono</td>
                    <td class="mayus linea" style="width: 30%;">{{ $persona->telefono ?: '-' }}</td>
                    <td class="etiqueta linea" style="width: 20%;">Celular</td>
                    <td>{{ $persona->celular ?: '-' }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 25%;">Estado Civil</td>
                    <td class="mayus linea" style="width: 30%;">{{ $persona->estado_civil }}</td>
                    <td class="etiqueta linea" style="width: 25%;">Nº de Hijos</td>
                    <td>{{ $persona->numero_hijos ?? 0 }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 28%;">Fecha Nacimiento</td>
                    <td>{{ $fecha($persona->fecha_nacimiento) }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 28%;">Lugar Nacimiento</td>
                    <td class="mayus">Provincia: {{ $persona->lugar_nacimiento_provincia ?: '-' }}&nbsp;&nbsp;&nbsp;Ciudad: {{ $persona->lugar_nacimiento_ciudad ?: '-' }}</td>
                </tr></table>
                <table class="fila con-linea"><tr>
                    <td class="etiqueta linea" style="width: 28%;">Correo Personal</td>
                    <td>{{ $persona->email ?: '-' }}</td>
                </tr></table>
                <table class="fila"><tr>
                    <td class="etiqueta linea" style="width: 28%;">Domicilio actual</td>
                    <td class="mayus">{{ $persona->direccion_actual ?: '-' }}</td>
                </tr></table>
            </td>
            <td class="foto-celda">
                <table class="foto"><tr><td>
                    @if ($foto)
                        <img src="{{ $foto }}" alt="Fotografía">
                    @else
                        <span class="foto-vacia">Fotografía</span>
                    @endif
                </td></tr></table>
            </td>
        </tr>
    </table>

    <table class="seccion">
        <tr><td class="seccion-titulo">Información Institucional</td></tr>
        <tr><td>
            <table class="fila"><tr>
                <td class="etiqueta linea" style="width: 10%;">Fecha Ingreso</td>
                <td class="mayus linea" style="width: 15%;">{{ $fecha($persona->fecha_ingreso_fundacion) }}</td>
                <td class="etiqueta linea" style="width: 10%;">Cargo</td>
                <td class="mayus linea" style="width: 35%;">{{ $persona->cargo_actual ?: '-' }}</td>
                <td class="etiqueta linea" style="width: 10%;">Oficina</td>
                <td class="mayus">{{ $persona->oficina_actual ?: '-' }}</td>
            </tr></table>
        </td></tr>
    </table>

    @if ($persona->latitude && $persona->longitude)
        <table class="seccion">
            <tr><td class="seccion-titulo">Croquis de Domicilio</td></tr>
            <tr><td class="croquis">
                @if ($mapa)
                    <img src="{{ $mapa }}" alt="Croquis de domicilio">
                @else
                    <div class="vacio">Croquis o mapa no disponible</div>
                @endif
            </td></tr>
        </table>
    @endif

    <table class="seccion">
        <tr><td class="seccion-titulo">Información Profesional</td></tr>
        <tr><td>
            @if ($persona->estudios->isEmpty())
                <div class="vacio">No registra información profesional cargada.</div>
            @else
                <table class="tabla">
                    <tr class="con-linea">
                        <th class="linea" style="width: 20%;">Instrucción</th>
                        <th class="linea" style="width: 45%;">Título Obtenido</th>
                        <th class="linea" style="width: 25%;">Institución</th>
                        <th>Año</th>
                    </tr>
                    @foreach ($persona->estudios as $estudio)
                        <tr class="{{ $fila($loop) }}">
                            <td class="mayus linea">{{ $estudio->tipo }}</td>
                            <td class="mayus linea">{{ $estudio->titulo_obtenido }}</td>
                            <td class="mayus linea">{{ $estudio->institucion }}</td>
                            <td class="centro">{{ $estudio->anio }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td></tr>
    </table>

    <table class="seccion">
        <tr><td class="seccion-titulo">Experiencia Laboral</td></tr>
        <tr><td>
            @if ($persona->experienciasLaborales->isEmpty())
                <div class="vacio">No registra experiencia laboral.</div>
            @else
                <table class="tabla">
                    <tr class="con-linea">
                        <th class="linea" style="width: 40%;">Institución</th>
                        <th class="linea" style="width: 30%;">Cargo</th>
                        <th>Fecha ingreso y salida</th>
                    </tr>
                    @foreach ($persona->experienciasLaborales as $experiencia)
                        <tr class="{{ $fila($loop) }}">
                            <td class="mayus linea">{{ $experiencia->institucion }}</td>
                            <td class="mayus linea">{{ $experiencia->cargo }}</td>
                            <td class="centro">{{ $fecha($experiencia->fecha_inicio) }} - {{ $experiencia->fecha_fin ? $fecha($experiencia->fecha_fin) : 'Actualidad' }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td></tr>
    </table>

    <table class="seccion">
        <tr><td class="seccion-titulo">Referencias Laborales</td></tr>
        <tr><td>
            @if ($persona->referenciasLaborales->isEmpty())
                <div class="vacio">No registra referencias laborales.</div>
            @else
                <table class="tabla">
                    <tr class="con-linea">
                        <th class="linea" style="width: 40%;">Institución</th>
                        <th class="linea" style="width: 35%;">Nombre</th>
                        <th>Teléfono / Celular</th>
                    </tr>
                    @foreach ($persona->referenciasLaborales as $referencia)
                        <tr class="{{ $fila($loop) }}">
                            <td class="mayus linea">{{ $referencia->institucion ?: 'N/A' }}</td>
                            <td class="mayus linea">{{ $referencia->nombre_referente }}</td>
                            <td class="centro">{{ $referencia->telefono_celular }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td></tr>
    </table>

    <table class="seccion">
        <tr><td class="seccion-titulo">Datos familiares</td></tr>
        <tr><td>
            @if ($familiares->isEmpty())
                <div class="vacio">No registra datos familiares.</div>
            @else
                <table class="tabla">
                    <tr class="con-linea">
                        <th class="linea" style="width: 50%;">Nombre</th>
                        <th class="linea" style="width: 30%;">Parentesco</th>
                        <th>Edad</th>
                    </tr>
                    @foreach ($familiares as $familiar)
                        <tr class="{{ $fila($loop) }}">
                            <td class="mayus linea">{{ $nombre($familiar) }}</td>
                            <td class="mayus centro linea">{{ $familiar->parentesco_relacion }}</td>
                            <td class="centro">{{ $familiar->edad ?: '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td></tr>
    </table>

    <table class="seccion">
        <tr><td class="seccion-titulo">Referencias Personales (Al menos 3 referencias)</td></tr>
        <tr><td>
            @if ($personales->isEmpty())
                <div class="vacio">No registra referencias personales.</div>
            @else
                <table class="tabla">
                    <tr class="con-linea">
                        <th class="linea" style="width: 45%;">Nombre</th>
                        <th class="linea" style="width: 30%;">Parentesco / Relación</th>
                        <th>Teléfono / Celular</th>
                    </tr>
                    @foreach ($personales as $contacto)
                        <tr class="{{ $fila($loop) }}">
                            <td class="mayus linea">{{ $nombre($contacto) }}</td>
                            <td class="mayus centro linea">{{ $contacto->parentesco_relacion }}</td>
                            <td class="centro">{{ $contacto->telefono_celular }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td></tr>
    </table>

    <table class="firmas">
        <tr>
            <td><div class="linea-firma"></div>Firma</td>
            <td class="espacio"></td>
            <td><div class="linea-firma"></div>V°B° Talento Humano</td>
        </tr>
    </table>
</body>
</html>
