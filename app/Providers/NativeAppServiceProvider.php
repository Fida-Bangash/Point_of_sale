<?php

namespace App\Providers;

use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Menu;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // An empty menu removes the default File / Edit / View / Window / Help bar.
        // It must never stop the window from opening, so a failure is only logged.
        try {
            Menu::create();
        } catch (\Throwable $e) {
            report($e);
        }

        // hideMenu() also hides the bar if it ever comes back (Alt shows it temporarily)
        Window::open()->hideMenu();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}