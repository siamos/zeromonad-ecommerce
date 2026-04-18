import { createApp, h } from 'vue'
import { createInertiaApp, usePage } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import '../css/app.css'

createInertiaApp({
    title: (title) => {
        const page = usePage()
        const siteName = page?.props?.site_name ?? 'ZeroMonad'
        return title ? `${title} — ${siteName}` : siteName
    },

    resolve: (name) => {
        const pages = import.meta.glob('./Themes/**/*.vue')

        return (initialPage) => {
            const theme = initialPage?.props?.current_theme ?? 'Products'
            const themePath    = `./Themes/${theme}/pages/${name}.vue`
            const fallbackPath = `./Themes/Products/pages/${name}.vue`

            if (pages[themePath]) {
                return pages[themePath]()
            }

            if (pages[fallbackPath]) {
                return pages[fallbackPath]()
            }

            // Last resort: try without theme nesting (for shared pages like Auth)
            return resolvePageComponent(
                `./Themes/Products/pages/${name}.vue`,
                import.meta.glob('./Themes/Products/pages/**/*.vue')
            )
        }
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el)
    },

    progress: {
        color: '#6366f1',
    },
})
