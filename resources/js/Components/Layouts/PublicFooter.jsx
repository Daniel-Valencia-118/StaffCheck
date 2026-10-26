import React from 'react';

export default function PublicFooter({ institucion }) {
    return (
        <footer className="w-full py-6 px-6 text-xs border-t border-gray-200 bg-white">
            <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-gray-500 text-center sm:text-left">
                <span>&copy; {new Date().getFullYear()} StaffCheck &bull; {institucion}. Todos los derechos reservados.</span>
                <span className="text-gray-400">Desarrollado por &#8203; 
                    <a href="mailto:danielvalalay17@gmail.com" target="_blank" rel="noopener noreferrer" className="text-gray-400 hover:text-gray-500">
                        Daniel Valencia Ticona
                    </a>
                </span>
            </div>
        </footer>
    );
}
