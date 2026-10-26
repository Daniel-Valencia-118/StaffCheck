import React from 'react';

export default function Logo({ className = 'text-xl', claro = false }) {
    return (
        <span className={`font-extrabold tracking-tight leading-none ${claro ? 'text-white' : 'text-marca-800'} ${className}`}>
            StaffCheck
        </span>
    );
}
