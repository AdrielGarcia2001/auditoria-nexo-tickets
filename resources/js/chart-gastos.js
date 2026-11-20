import Chart from 'chart.js/auto';

/**
 * Gráficos del panel de control de gasto: se puede elegir entre
 * torta (doughnut) y barras. Los datos vienen de <script id="datos-gastos">.
 */
function inicializarGraficosGastos() {
    const tag = document.getElementById('datos-gastos');
    if (!tag) return;

    let datos;
    try {
        datos = JSON.parse(tag.textContent);
    } catch (e) {
        console.warn('No se pudieron leer los datos de gastos para los gráficos.', e);
        return;
    }

    const PALETA = [
        '#2563eb', '#7c3aed', '#0891b2', '#db2777', '#ea580c',
        '#16a34a', '#d97706', '#dc2626', '#0d9488', '#4f46e5',
    ];

    // tipo elegido (torta por defecto)
    const selector = document.getElementById('tipo-grafico');
    const tipo = selector ? selector.value : 'torta';

    // mostrar/ocultar contenedores según el tipo
    const contChart = document.getElementById('grafico-chart');
    const contLineal = document.getElementById('grafico-lineal');
    if (contChart) contChart.style.display = tipo === 'lineal' ? 'none' : '';
    if (contLineal) contLineal.style.display = tipo === 'lineal' ? '' : 'none';

    if (tipo === 'lineal') {
        // las barras horizontales son HTML puro, no necesitan Chart.js
        if (window.__chartsGastosInstancias) {
            window.__chartsGastosInstancias.forEach((ch) => ch.destroy());
        }
        window.__chartsGastosInstancias = [];
        return;
    }

    const tipoChart = tipo === 'barra' ? 'bar' : 'doughnut';

    // destruir instancias previas (Livewire puede re-disparar la inicialización)
    if (window.__chartsGastosInstancias) {
        window.__chartsGastosInstancias.forEach((ch) => ch.destroy());
    }
    window.__chartsGastosInstancias = [];

    const dibujar = (canvasId, etiquetas, valores) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !valores.length) return;

        const esTorta = tipoChart === 'doughnut';

        const configuracion = {
            type: tipoChart,
            data: {
                labels: etiquetas,
                datasets: [{
                    data: valores,
                    backgroundColor: etiquetas.map((_, i) => PALETA[i % PALETA.length]),
                    borderColor: esTorta ? '#ffffff' : PALETA.map((_, i) => PALETA[i % PALETA.length]),
                    borderWidth: esTorta ? 2 : 0,
                    hoverOffset: esTorta ? 8 : 0,
                    borderRadius: esTorta ? 0 : 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: esTorta ? '62%' : undefined,
                plugins: {
                    legend: {
                        display: esTorta,
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 14,
                            usePointStyle: true,
                            font: { size: 11 },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total ? (ctx.parsed / total * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: $${Number(ctx.parsed).toLocaleString('es-AR', { minimumFractionDigits: 2 })} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        };

        // escalas solo para barras
        if (!esTorta) {
            configuracion.options.scales = {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => '$' + Number(v).toLocaleString('es-AR'),
                        font: { size: 10 },
                    },
                },
            };
        }

        const chart = new Chart(canvas, configuracion);
        window.__chartsGastosInstancias.push(chart);
    };

    try {
        dibujar('chart-gasto-mes', datos.meses.etiquetas, datos.meses.valores);
        dibujar('chart-gasto-insumo', datos.insumos.etiquetas, datos.insumos.valores);
        window.__chartGastosOk = true;
    } catch (e) {
        window.__chartGastosError = String(e);
        console.error('Error al inicializar gráficos de gastos:', e);
    }
}

function alCambiarTipo() {
    inicializarGraficosGastos();
}

/** Cambia entre torta y barras (llamado desde los botones de la vista). */
window.cambiarTipoGrafico = (tipo) => {
    const selector = document.getElementById('tipo-grafico');
    if (selector) {
        selector.value = tipo;
        selector.dispatchEvent(new Event('change'));
    }

    const botones = { torta: 'btn-torta', barra: 'btn-barra', lineal: 'btn-lineal' };
    const activo = 'background:#0f172a;color:#ffffff;box-shadow:0 1px 2px rgba(0,0,0,.15);';
    const inactivo = 'background:transparent;color:#475569;';

    Object.entries(botones).forEach(([clave, id]) => {
        const btn = document.getElementById(id);
        if (btn) btn.setAttribute('style', 'border-radius:6px;padding:8px 16px;font-size:14px;font-weight:600;' + (tipo === clave ? activo : inactivo));
    });
};

// inicializar al cargar el DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        inicializarGraficosGastos();
        const selector = document.getElementById('tipo-grafico');
        if (selector) selector.addEventListener('change', alCambiarTipo);
    });
} else {
    inicializarGraficosGastos();
    const selector = document.getElementById('tipo-grafico');
    if (selector) selector.addEventListener('change', alCambiarTipo);
}

window.addEventListener('livewire:navigated', () => {
    inicializarGraficosGastos();
    const selector = document.getElementById('tipo-grafico');
    if (selector) selector.addEventListener('change', alCambiarTipo);
});
