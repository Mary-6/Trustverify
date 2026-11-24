import './bootstrap';

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-password-toggle]');

    if (button) {
        event.preventDefault();

        const input = button.parentElement?.querySelector('input');

        if (!input) {
            return;
        }

        const show = input.type === 'password';

        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        button.querySelectorAll('[data-password-icon]').forEach((icon) => {
            icon.classList.toggle('hidden', show ? icon.dataset.passwordIcon === 'show' : icon.dataset.passwordIcon === 'hide');
        });

        return;
    }

    const dropdownToggles = document.querySelectorAll('[data-nav-dropdown-toggle]');
    const toggle = event.target.closest('[data-nav-dropdown-toggle]');

    const closeAll = () => {
        document.querySelectorAll('[data-nav-dropdown-menu]').forEach((menu) => menu.classList.add('hidden'));
        document.querySelectorAll('[data-nav-dropdown-toggle]').forEach((item) => item.setAttribute('aria-expanded', 'false'));
    };

    if (toggle) {
        event.preventDefault();
        const dropdown = toggle.closest('[data-nav-dropdown]');
        const menu = dropdown?.querySelector('[data-nav-dropdown-menu]');
        const wasOpen = menu && !menu.classList.contains('hidden');

        closeAll();

        if (menu && !wasOpen) {
            menu.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        }

        return;
    }

    if (dropdownToggles.length && !event.target.closest('[data-nav-dropdown]')) {
        closeAll();
    }
});

function syncThemeIcons() {
    const dark = document.documentElement.classList.contains('dark');

    document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
        const isSun = icon.dataset.themeIcon === 'sun';
        icon.classList.toggle('hidden', isSun ? !dark : dark);
    });
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-theme-toggle]');

    if (!toggle) {
        return;
    }

    const dark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', dark ? 'dark' : 'light');
    syncThemeIcons();
});

syncThemeIcons();
