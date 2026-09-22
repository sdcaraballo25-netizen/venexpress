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
});
