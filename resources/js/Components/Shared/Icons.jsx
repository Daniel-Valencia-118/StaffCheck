import React from 'react';

const baseSvgProps = {
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: '2',
    strokeLinecap: 'round',
    strokeLinejoin: 'round',
    className: 'w-4 h-4',
    'aria-hidden': 'true',
};

export const UsersIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
);

export const MapPinIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" />
    </svg>
);

export const BuildingIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" /><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" /><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" /><path d="M10 6h4" /><path d="M10 10h4" /><path d="M10 14h4" /><path d="M10 18h4" />
    </svg>
);

export const UserIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
    </svg>
);

export const UserRoundIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <circle cx="12" cy="8" r="5" /><path d="M20 21a8 8 0 0 0-16 0" />
    </svg>
);

export const ShieldIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path d="m9 12 2 2 4-4" />
    </svg>
);

export const SettingsIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" /><circle cx="12" cy="12" r="3" />
    </svg>
);

export const ClipboardIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <rect x="8" y="2" width="8" height="4" rx="1" /><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path d="M9 12h6" /><path d="M9 16h6" />
    </svg>
);

export const ServerIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <rect x="2" y="2" width="20" height="8" rx="2" /><rect x="2" y="14" width="20" height="8" rx="2" /><path d="M6 6h.01" /><path d="M6 18h.01" />
    </svg>
);

export const UserCircleIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <circle cx="12" cy="12" r="10" /><circle cx="12" cy="10" r="3" /><path d="M7 20.66V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.66" />
    </svg>
);

export const PersonalIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
);

export const UserCogIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <circle cx="9" cy="7" r="4" /><path d="M2 21v-2a4 4 0 0 1 4-4h6" /><circle cx="18" cy="17" r="2.5" /><path d="M18 13v1.5" /><path d="M18 19.5V21" /><path d="M21.5 17H20" /><path d="M16 17h-1.5" />
    </svg>
);

export const MenuIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />
    </svg>
);

export const XIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M18 6 6 18" /><path d="m6 6 12 12" />
    </svg>
);

export const ChevronLeftIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="m15 18-6-6 6-6" />
    </svg>
);

export const ChevronRightIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="m9 18 6-6-6-6" />
    </svg>
);

export const PanelLeftIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <rect x="3" y="3" width="18" height="18" rx="2" /><path d="M9 3v18" />
    </svg>
);

export const LogoutIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="m16 17 5-5-5-5" /><path d="M21 12H9" />
    </svg>
);

export const DownloadIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="m7 10 5 5 5-5" /><path d="M12 15V3" />
    </svg>
);

export const FileTextIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" />
    </svg>
);

export const PencilIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
    </svg>
);

export const TrashIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M3 6h18" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
    </svg>
);

export const SearchIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
    </svg>
);

export const SortIcon = ({ direccion, ...props }) => (
    <svg {...baseSvgProps} {...props}>
        {direccion !== 'desc' && <path d="m7 9 5-5 5 5" opacity={direccion === 'asc' ? 1 : 0.35} />}
        {direccion !== 'asc' && <path d="m7 15 5 5 5-5" opacity={direccion === 'desc' ? 1 : 0.35} />}
    </svg>
);

export const ExcelIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" /><path d="M14 2v6h6" /><path d="m8 13 4 5" /><path d="m12 13-4 5" /><path d="M14 13h2" /><path d="M14 17h2" />
    </svg>
);

export const PlusIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M12 5v14" /><path d="M5 12h14" />
    </svg>
);

export const EyeIcon = (props) => (
    <svg {...baseSvgProps} {...props}>
        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" /><circle cx="12" cy="12" r="3" />
    </svg>
);
