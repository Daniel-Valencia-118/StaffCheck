import React from 'react';
import { MapContainer, TileLayer, Marker } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './leafletIcono';

export default function MapViewer({ lat, lng, height = '250px' }) {
    if (!lat || !lng) {
        return <p className="text-gray-400 text-sm italic">Sin ubicación registrada.</p>;
    }

    const posicion = [parseFloat(lat), parseFloat(lng)];

    return (
        <div className="w-full border border-gray-200 rounded-lg overflow-hidden z-0 relative" style={{ height }}>
            <MapContainer
                center={posicion}
                zoom={18}
                dragging={false}
                scrollWheelZoom={false}
                doubleClickZoom={false}
                zoomControl={false}
                style={{ height: '100%', width: '100%' }}
            >
                <TileLayer
                    attribution='&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
                    url="https://tile.openstreetmap.org/{z}/{x}/{y}.png"
                    maxZoom={19}
                />
                <Marker position={posicion} />
            </MapContainer>
        </div>
    );
}
