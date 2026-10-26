import React from 'react';
import Card from '../../../Components/Forms/Card';

export default function TablaDetalle({ titulo, columnas, filas, vacio }) {
    return (
        <Card title={titulo} className="">
            {filas.length > 0 ? (
                <div className="overflow-x-auto -mx-2">
                    <table className="w-full text-left text-sm text-gray-700">
                        <thead className="text-marca-800 font-semibold uppercase text-xs">
                            <tr className="border-b border-gray-200">
                                {columnas.map((columna) => (
                                    <th key={columna.titulo} scope="col" className="py-2 px-2 whitespace-nowrap">{columna.titulo}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 uppercase">
                            {filas.map((fila, indice) => (
                                <tr key={fila.id ?? indice}>
                                    {columnas.map((columna, posicion) => (
                                        <td key={columna.titulo} className={`py-2 px-2 ${posicion === 0 ? 'font-medium text-gray-900' : ''}`}>
                                            {columna.valor(fila) || '-'}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : (
                <p className="text-sm text-gray-500 italic">{vacio}</p>
            )}
        </Card>
    );
}
