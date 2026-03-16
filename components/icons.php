<?php
/**
 * Icon helper: returns small inline SVGs with consistent stroke style.
 */

if (!function_exists('tcad_icon')) {
    /**
     * Render an inline SVG icon.
     *
     * @param string $name   Icon key (dashboard, cells, calendar, offering, servers, materials,
     *                       leadership, notifications, audit, reports, profile, settings, logout).
     * @param string $class  Tailwind-compatible size/color classes.
     * @return string        SVG markup.
     */
    function tcad_icon(string $name, string $class = 'w-5 h-5'): string
    {
        $base = 'fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"';

        switch ($name) {
            case 'dashboard':
                return "<svg class=\"$class\" $base><path d=\"M4 20h16\"/><path d=\"M7 16V9\"/><path d=\"M12 16V5\"/><path d=\"M17 16v-7\"/></svg>";

            case 'cells':
                return "<svg class=\"$class\" $base><rect x=\"3\" y=\"3\" width=\"8\" height=\"8\" rx=\"1.4\"/><rect x=\"13\" y=\"3\" width=\"8\" height=\"8\" rx=\"1.4\"/><rect x=\"3\" y=\"13\" width=\"8\" height=\"8\" rx=\"1.4\"/><rect x=\"13\" y=\"13\" width=\"8\" height=\"8\" rx=\"1.4\"/></svg>";

            case 'calendar':
                return "<svg class=\"$class\" $base><rect x=\"3.5\" y=\"5\" width=\"17\" height=\"15\" rx=\"2\"/><path d=\"M8 3v4\"/><path d=\"M16 3v4\"/><path d=\"M3.5 9.5h17\"/><circle cx=\"9\" cy=\"13\" r=\"1.1\"/><circle cx=\"15\" cy=\"13\" r=\"1.1\"/><circle cx=\"12\" cy=\"17\" r=\"1.1\"/></svg>";

            case 'offering':
                return "<svg class=\"$class\" $base><rect x=\"3.5\" y=\"8\" width=\"17\" height=\"8\" rx=\"1.8\"/><circle cx=\"12\" cy=\"12\" r=\"2.4\"/><path d=\"M7.2 12h.01\"/><path d=\"M16.8 12h.01\"/></svg>";

            case 'servers':
                return "<svg class=\"$class\" $base><circle cx=\"10\" cy=\"8\" r=\"3.5\"/><path d=\"M16.5 11.5a3 3 0 100-6\"/><path d=\"M4.5 20v-1.5A4.5 4.5 0 019 14h2a4.5 4.5 0 014.5 4.5V20\"/><path d=\"M18.5 20v-1.2a3.5 3.5 0 00-2.8-3.4\"/></svg>";

            case 'materials':
                return "<svg class=\"$class\" $base><path d=\"M12 5.5c-1-.8-2.4-1.3-3.9-1.3H5a2 2 0 00-2 2v11.3a2 2 0 002 2h3.1c1.5 0 2.9.5 3.9 1.3V5.5z\"/><path d=\"M12 5.5c1-.8 2.4-1.3 3.9-1.3H19a2 2 0 012 2v11.3a2 2 0 01-2 2h-3.1c-1.5 0-2.9.5-3.9 1.3V5.5z\"/></svg>";

            case 'leadership':
                return "<svg class=\"$class\" $base><path d=\"M12 21c-3.5-1.2-7-3.6-7-8.4V6.5L12 4l7 2.5v6.1c0 4.8-3.5 7.2-7 8.4z\"/><path d=\"M9.5 12.5l1.8 1.8 3.2-3.6\"/></svg>";

            case 'notifications':
                return "<svg class=\"$class\" $base><path d=\"M6 9a6 6 0 0112 0v3.8l1.2 2.4a1 1 0 01-.9 1.5H5.7a1 1 0 01-.9-1.5L6 12.8V9z\"/><path d=\"M10 19a2 2 0 004 0\"/></svg>";

            case 'audit':
                return "<svg class=\"$class\" $base><path d=\"M9 4h6a2 2 0 012 2v12a2 2 0 01-2 2H9a2 2 0 01-2-2V6a2 2 0 012-2z\"/><path d=\"M9 9h6\"/><path d=\"M9 13h6\"/><path d=\"M9 17h3.5\"/><path d=\"M11 3.5a1.5 1.5 0 013 0\"/></svg>";

            case 'reports':
                return "<svg class=\"$class\" $base><path d=\"M12 3a9 9 0 019 9H12V3z\"/><path d=\"M12 3a9 9 0 00-9 9 9 9 0 0014.14 7.32L12 12\"/></svg>";

            case 'profile':
                return "<svg class=\"$class\" $base><path d=\"M12 21a9 9 0 100-18 9 9 0 000 18z\"/><path d=\"M12 12a3 3 0 10-3-3 3 3 0 003 3z\"/><path d=\"M7.5 17.5a4.5 4.5 0 019 0\"/></svg>";

            case 'settings':
                return "<svg class=\"$class\" $base><path d=\"M12 15.5a3.5 3.5 0 110-7 3.5 3.5 0 010 7z\"/><path d=\"M19.5 12a7.5 7.5 0 01-.08 1.08l1.53 1.2-1 1.73-1.82-.54a7.5 7.5 0 01-1.5.87l-.28 1.9h-2l-.28-1.9a7.5 7.5 0 01-1.5-.87l-1.82.54-1-1.73 1.53-1.2A7.5 7.5 0 014.5 12a7.5 7.5 0 01.08-1.08l-1.53-1.2 1-1.73 1.82.54a7.5 7.5 0 011.5-.87l.28-1.9h2l.28 1.9a7.5 7.5 0 011.5.87l1.82-.54 1 1.73-1.53 1.2c.05.36.08.72.08 1.08z\"/></svg>";

            case 'logout':
                return "<svg class=\"$class\" $base><path d=\"M9 7l-5 5 5 5\"/><path d=\"M4 12h11\"/><path d=\"M15 5h4a1 1 0 011 1v12a1 1 0 01-1 1h-4\"/></svg>";

            default:
                return '';
        }
    }
}

