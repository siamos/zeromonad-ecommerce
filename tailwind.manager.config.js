import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Manager/**/*.php',
        './resources/views/filament/manager/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
