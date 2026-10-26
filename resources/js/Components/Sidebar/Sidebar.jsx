import React from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import SidebarFooter from './SidebarFooter';
import Logo from '../Shared/Logo';
import usePuede from '../../hooks/usePuede';
import {
    PersonalIcon, UserCogIcon, ShieldIcon, SettingsIcon, ClipboardIcon, ServerIcon, FileTextIcon, UserCircleIcon,
    PanelLeftIcon, XIcon,
} from '../Shared/Icons';

const MENU = [
    { seccion: 'Gestión', title: 'Personal', ruta: 'personal.index', activo: 'personal.*', permiso: 'personal.ver', icon: PersonalIcon },
    { seccion: 'Administración', title: 'Usuarios', ruta: 'usuarios.index', activo: 'usuarios.*', permiso: 'usuarios.ver', icon: UserCogIcon },
    { seccion: 'Administración', title: 'Roles y permisos', ruta: 'roles.index', activo: 'roles.*', permiso: 'roles.gestionar', icon: ShieldIcon },
    { seccion: 'Administración', title: 'Configuración', ruta: 'configuracion.index', activo: 'configuracion.*', permiso: 'configuracion.gestionar', icon: SettingsIcon },
    { seccion: 'Administración', title: 'Auditoría', ruta: 'auditoria.index', activo: 'auditoria.*', permiso: 'auditoria.ver', icon: ClipboardIcon },
    { seccion: 'Administración', title: 'Sistema', ruta: 'sistema.index', activo: 'sistema.*', permiso: 'sistema.gestionar', icon: ServerIcon },
    { seccion: 'Cuenta', title: 'Mi ficha', ruta: 'mi_ficha.edit', activo: 'mi_ficha.*', permiso: 'mi_ficha.editar', icon: FileTextIcon },
    { seccion: 'Cuenta', title: 'Mi cuenta', ruta: 'cuenta', activo: 'cuenta', permiso: 'panel.acceder', icon: UserCircleIcon },
];

const TITULOS = {
    admin: 'Administración',
    encargado: 'Recursos Humanos',
};

export default function Sidebar({ colapsado, onColapsar, abierto, onCerrar }) {
    const user = usePage().props.auth?.user ?? {};
    const puede = usePuede();

    const menuItems = MENU.filter((item) => puede(item.permiso));
    const titulo = TITULOS[user.roles?.[0]] ?? 'Panel';
    const soloExpandido = colapsado ? 'md:hidden' : '';

    return (
        <aside
            id="menu-lateral"
            className={`fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white border-r border-gray-200 shadow-sm
                transition-[transform,width] duration-200 ease-in-out
                md:relative md:z-auto md:translate-x-0 ${colapsado ? 'md:w-18' : 'md:w-64'}
                ${abierto ? 'translate-x-0' : '-translate-x-full'}`}
        >
            <div className={`h-16 flex items-center gap-2 border-b border-gray-100 shrink-0 px-4 ${colapsado ? 'md:px-0 md:justify-center' : ''}`}>
                <div className={`min-w-0 flex-1 ${soloExpandido}`}>
                    <Logo className="text-xl block" />
                    <p className="text-[11px] font-medium text-gray-500 truncate mt-1">{titulo}</p>
                </div>
                <button
                    type="button"
                    onClick={onColapsar}
                    title={colapsado ? 'Expandir menú' : 'Contraer menú'}
                    aria-label={colapsado ? 'Expandir menú' : 'Contraer menú'}
                    aria-expanded={!colapsado}
                    aria-controls="menu-lateral"
                    className="hidden md:inline-flex p-2 rounded-lg text-gray-500 hover:text-marca-700 hover:bg-marca-50 cursor-pointer"
                >
                    <PanelLeftIcon className="w-5 h-5" />
                </button>
                <button
                    type="button"
                    onClick={onCerrar}
                    aria-label="Cerrar menú"
                    className="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 cursor-pointer"
                >
                    <XIcon className="w-5 h-5" />
                </button>
            </div>

            <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto overflow-x-hidden" aria-label="Menú principal">
                {menuItems.map((item, index) => {
                    const activo = route().current(item.activo);
                    const nuevaSeccion = item.seccion !== menuItems[index - 1]?.seccion;
                    const Icono = item.icon;

                    return (
                        <React.Fragment key={item.ruta}>
                            {nuevaSeccion && (
                                <>
                                    <p className={`px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400 ${index > 0 ? 'pt-4' : ''} ${soloExpandido}`}>
                                        {item.seccion}
                                    </p>
                                    {colapsado && index > 0 && <hr className="hidden md:block my-2 border-gray-100" />}
                                </>
                            )}
                            <Link
                                href={route(item.ruta)}
                                title={colapsado ? item.title : undefined}
                                aria-current={activo ? 'page' : undefined}
                                className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                    ${colapsado ? 'md:justify-center' : ''}
                                    ${activo ? 'bg-marca-700 text-white shadow-sm' : 'text-gray-600 hover:bg-marca-50 hover:text-marca-700'}`}
                            >
                                <Icono className={`w-5 h-5 shrink-0 ${activo ? 'text-white' : 'text-gray-400'}`} />
                                <span className={`whitespace-nowrap ${soloExpandido}`}>{item.title}</span>
                            </Link>
                        </React.Fragment>
                    );
                })}
            </nav>

            <SidebarFooter user={user} colapsado={colapsado} onLogout={() => router.post(route('logout'))} />
        </aside>
    );
}
