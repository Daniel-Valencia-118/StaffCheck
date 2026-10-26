import React, { useEffect } from 'react';
import { MapContainer, TileLayer, Marker, useMapEvents, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './leafletIcono';

const CENTRO_LA_PAZ = [-16.495581349984814, -68.13352637564697];

function RecentrarMapa({ lat, lng }) {
    const map = useMap();

    useEffect(() => {
        if (lat && lng) {
            map.setView([lat, lng], map.getZoom());
        }
    }, [lat, lng, map]);

    return null;
}

function SelectorDePunto({ onChange }) {
    useMapEvents({
        click(e) {
            onChange({ lat: e.latlng.lat, lng: e.latlng.lng });
        },
    });

    return null;
}

export default function MapPicker({ lat, lng, onChange, height = '350px' }) {
    return (
        <div className="w-full border border-gray-300 rounded-lg overflow-hidden z-0" style={{ height }}>
            <MapContainer center={lat && lng ? [lat, lng] : CENTRO_LA_PAZ} zoom={17} style={{ height: '100%', width: '100%' }}>
                <TileLayer
                    attribution='&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
                    url="https://tile.openstreetmap.org/{z}/{x}/{y}.png"
                    maxZoom={19}
                />
                <SelectorDePunto onChange={onChange} />
                <RecentrarMapa lat={lat} lng={lng} />
                {lat && lng && <Marker position={[lat, lng]} />}
            </MapContainer>
        </div>
    );
}
