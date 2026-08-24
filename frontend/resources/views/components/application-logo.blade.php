@props(['onDark' => false])

<svg viewBox="0 0 200 60" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <!-- Libro -->
    <g transform="translate(10, 10)">
        <rect x="0" y="0" width="40" height="40" rx="3"
            fill="{{ $onDark ? '#FFFFFF' : '#63A355' }}"
            stroke="{{ $onDark ? '#C9A94E' : '#4A7D3F' }}" stroke-width="1.5"/>
        <line x1="8" y1="8" x2="8" y2="32" stroke="{{ $onDark ? '#2A5F23' : '#FFFFFF' }}" stroke-width="2" stroke-linecap="round"/>
        <line x1="16" y1="8" x2="16" y2="32" stroke="{{ $onDark ? '#2A5F23' : '#FFFFFF' }}" stroke-width="1.5" stroke-linecap="round" opacity="0.6"/>
        <line x1="24" y1="8" x2="24" y2="32" stroke="{{ $onDark ? '#2A5F23' : '#FFFFFF' }}" stroke-width="1.5" stroke-linecap="round" opacity="0.6"/>
        <line x1="32" y1="8" x2="32" y2="32" stroke="{{ $onDark ? '#2A5F23' : '#FFFFFF' }}" stroke-width="1.5" stroke-linecap="round" opacity="0.6"/>
        <rect x="30" y="0" width="10" height="40" rx="3" fill="#C9A94E"/>
    </g>
    <!-- Texto SIGCB-QR -->
    <text x="60" y="28" font-family="Poppins, sans-serif" font-weight="700" font-size="16" fill="{{ $onDark ? '#FFFFFF' : '#63A355' }}">SIGCB</text>
    <text x="118" y="28" font-family="Poppins, sans-serif" font-weight="300" font-size="16" fill="{{ $onDark ? '#F0D99A' : '#4A7D3F' }}">-QR</text>
    <text x="60" y="45" font-family="Poppins, sans-serif" font-weight="400" font-size="{{ $onDark ? '10' : '9' }}" fill="{{ $onDark ? '#FFFFFF' : '#C9A94E' }}" opacity="{{ $onDark ? '0.9' : '1' }}">Biblioteca Universitaria</text>
</svg>
