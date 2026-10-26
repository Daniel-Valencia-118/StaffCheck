import React from 'react';

export default function KpiCards({ cards }) {
    return (
        <div className="grid grid-cols-2 lg:grid-cols-5 gap-4">
            {cards.map((card) => {
                const Icon = card.icon;
                return (
                    <div key={card.label} className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5">
                        <div className="flex items-center justify-between gap-2">
                            <div>
                                <p className="text-gray-500 text-sm font-medium">{card.label}</p>
                                <p className="text-2xl sm:text-3xl font-bold text-marca-900 mt-1">{card.value}</p>
                            </div>
                            <div className={`h-11 w-11 rounded-full ${card.bg} hidden sm:flex items-center justify-center shrink-0`}>
                                <Icon className={`h-5 w-5 ${card.color}`} />
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
