import { usePage } from '@inertiajs/vue3'

export function useI18n() {
    const page = usePage()

    function t(key, replacements = {}) {
        let value = (page.props.translations ?? {})[key] ?? key
        for (const [k, v] of Object.entries(replacements)) {
            value = value.replace(`{${k}}`, v)
        }
        return value
    }

    return { t }
}
