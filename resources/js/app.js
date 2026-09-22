import './bootstrap';

/**
 * Store global del diálogo de confirmación (ver
 * resources/views/components/confirm-dialog.blade.php). Vive aquí, en el
 * bundle que carga una sola vez por sesión de navegador, en vez de en un
 * <script> inline dentro del componente: ese componente se incluye en el
 * <body> de cada layout, y wire:navigate (usado por el redirect de login y
 * por toda la navegación de los paneles) reemplaza el <body> sin volver a
 * ejecutar los <script> insertados ahí — así que el listener de
 * "alpine:init" nunca se re-disparaba tras el primer salto de página, y
 * cualquier botón "$store.confirm.open(...)" quedaba roto el resto de la
 * sesión. Registrado aquí, se ejecuta una vez en la primera carga real de
 * la sesión (login o cualquier página) y persiste en memoria durante toda
 * la navegación SPA posterior.
 */
document.addEventListener('alpine:init', () => {
    Alpine.store('confirm', {
        show: false,
        title: null,
        message: '',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar',
        variant: 'primary',
        onConfirm: null,

        open(options) {
            this.title = options.title ?? null;
            this.message = options.message ?? '¿Estás seguro?';
            this.confirmText = options.confirmText ?? 'Confirmar';
            this.cancelText = options.cancelText ?? 'Cancelar';
            this.variant = options.variant ?? 'primary';
            this.onConfirm = options.onConfirm ?? null;
            this.show = true;
        },

        confirmAction() {
            const action = this.onConfirm;
            this.show = false;
            if (typeof action === 'function') action();
        },

        cancel() {
            this.show = false;
        },
    });

    /**
     * Estado del modal de rastreo de guía en el sidebar del panel de
     * Cliente (ver resources/views/layouts/client.blade.php). Mismo
     * motivo que el store 'confirm' de arriba: antes vivía en un
     * <script> dentro del propio layout y wire:navigate lo dejaba sin
     * registrar tras el login.
     */
    Alpine.store('tracking', {
        src: '',
    });

    /**
     * Visor de imagen a pantalla completa para los adjuntos del chat
     * de pedido (ver components/image-lightbox.blade.php) — antes las
     * fotos abrían en una pestaña nueva. Mismo motivo que los stores
     * de arriba para vivir aquí en vez de en un <script> del layout.
     */
    Alpine.store('lightbox', {
        show: false,
        src: null,

        open(src) {
            this.src = src;
            this.show = true;
        },

        close() {
            this.show = false;
        },
    });
});
